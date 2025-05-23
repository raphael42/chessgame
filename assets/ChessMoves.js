import $ from 'jquery';
import 'jquery-ui/ui/widgets/draggable';
import 'jquery-ui/ui/widgets/droppable';
import { Chess } from 'chess.js';
import { lauchFireworks, placePieces, getKingPosition, promotionPiece, calculateMaterial } from './utils';
require('bootstrap');

var HISTORYINDEX = null;
var HISTORYINVIEW = false;

const chess = new Chess(FEN);
if (PGN !== null) {
    chess.loadPgn(PGN);
}

// For better visibility, set the socket params in JSON format and transform it to queryString with a function
const queryString = jsonToQueryString({
    idGame: IDGAME,
    playerType: PLAYERTYPE,
    color: PLAYERCOLOR.charAt(0),
    gameType: GAMETYPE,
    gameStatus: GAMESTATUS,
});

if (ENV === 'dev') {
    var socket = new WebSocket('ws://' + SERVERNAME + ':3001/ws/game?' + queryString);
} else {
    var socket = new WebSocket('wss://' + SERVERNAME + ':3001/ws/game?' + queryString);
}

placePieces(FEN, false, chess);

// Timer variables
const totalTimePlayer = PLAYERTIMELEFT * 1000; // Player time * 1000 to convert it to milliseconds
const totalTimeOpponent = OPPONENTTIMELEFT * 1000; // Opponent time * 1000 to convert it to milliseconds
let endTimePlayer = Date.now() + totalTimePlayer; // Player datetime when timer has to stop
let endTimeOpponent = Date.now() + totalTimeOpponent; // Opponent datetime when timer has to stop
let remainingTimePlayer = totalTimePlayer; // Player timeleft in milliseconds
let remainingTimeOpponent = totalTimeOpponent; // Opponent timeleft in milliseconds
let isRunningPlayer = false; // Player timer state. True if the player timer player is running. False otherwise
let isRunningOpponent = false; // Opponent timer state. True if the opponent timer is running. False otherwise
let animationFrame = null; // Reference to requestAnimationFrame

calculateMaterial(FEN,PLAYERCOLOR);

$(".timer-player").text(formatTime(totalTimePlayer));
$(".timer-opponent").text(formatTime(totalTimeOpponent));

if (totalTimePlayer === 0) {
    $('.timer-bottom').addClass('over');
}

if (totalTimeOpponent === 0) {
    $('.timer-top').addClass('over');
}

$(function() {
    if (chess.inCheck() === true) {
        let kingposition = null;
        kingposition = getKingPosition(FEN, chess.turn());

        $('#' + kingposition).addClass('in-check');
    }

    if (GAMESTATUS !== 'finished') {
        if (chess.turn() === 'w' && PLAYERCOLOR === 'w') {
            $('#title').html('C\'est votre tour ! | ' + SERVERNAME);
        } else {
            $('#title').html('En attente de l\'adversaire | ' + SERVERNAME);
        }
    }

    // Game with friend and the player is waiting for his opponent, display the modal
    if (GAMESTATUS === 'waiting-player' && GAMETYPE === 'with-friend') {
        $('#begining-with-friend-modal').modal('show');
    } else { // If not ...
        if ($('#begining-with-friend-modal').hasClass('show')) { // ... and the modal is open, close it
            $('#begining-with-friend-modal').modal('hide');
        }
    }

    // Random game and the player is waiting for his opponent, display the modal
    if (GAMESTATUS === 'waiting-player' && GAMETYPE === 'random') {
        $('#begining-random-modal').modal('show');
    } else { // If not ...
        if ($('#begining-random-modal').hasClass('show')) { // ... and the modal is open, close it
            $('#begining-random-modal').modal('hide');
        }
    }

    socket.addEventListener('open', function(e) {
        console.log('open', e);

        // Socket is open and the game is random or ranked and has now two player. Send to socket that the game is not available anymore
        if (GAMEUNAVAILABLE) {
            try {
                socket.send(JSON.stringify({
                    'method': 'unavailable',
                    'idGame': IDGAME,
                }));
            } catch (error) {
                console.log('Socket error', error);
            }
        }
    });

    socket.addEventListener('message', function(e) {
        var socketMessage = JSON.parse(e.data);

        console.log(socketMessage);

        // Disconnect
        if (typeof socketMessage.method !== 'undefined' && socketMessage.method === 'opponent_disconnect') {
            let playerFound = false;
            let opponentFound = false;
            let spectatorsNumber = 0;
            for (let i in socketMessage.connectedUsers) {
                // Not spectator and color is the same than the player one, then, the player is connected
                if (socketMessage.connectedUsers[i].playerType !== 'spectator' && socketMessage.connectedUsers[i].playerType === PLAYERCOLOR) {
                    playerFound = true;
                }

                // Not spectator and color is different than the player one, then, the opponent is connected
                if (socketMessage.connectedUsers[i].playerType !== 'spectator' && socketMessage.connectedUsers[i].playerType !== PLAYERCOLOR) {
                    opponentFound = true;
                }

                // Check the spectators number
                if (socketMessage.connectedUsers[i].playerType === 'spectator') {
                    spectatorsNumber++;
                }
            }

            // Update player connection status
            if (playerFound) {
                $('.player-connect').removeClass('user-disconnected');
                $('.player-connect').addClass('user-connected');
            } else {
                $('.player-connect').addClass('user-disconnected');
                $('.player-connect').removeClass('user-connected');
            }

            // update opponent status
            if (opponentFound) {
                $('.opponent-connect').removeClass('user-disconnected');
                $('.opponent-connect').addClass('user-connected');
            } else {
                $('.opponent-connect').addClass('user-disconnected');
                $('.opponent-connect').removeClass('user-connected');
            }

            // One or more spectators, remove d-none on div if there is one, and update the number
            if (spectatorsNumber > 0 && $('.spectators').hasClass('d-none')) {
                $('.spectators').removeClass('d-none');
            }

            if (spectatorsNumber === 0 && !$('.spectators').hasClass('d-none')){ // No spectators
                $('.spectators').addClass('d-none');
            }
            $('.spectators-number').html(spectatorsNumber);
            return;
        }

        // Connect
        if (typeof socketMessage.method !== 'undefined' && socketMessage.method === 'opponent_connect') {
            let playerFound = false;
            let opponentFound = false;
            let spectatorsNumber = 0;
            for (let i in socketMessage.connectedUsers) {
                // Not spectator and color is the same than the player one, then, the player is connected
                if (socketMessage.connectedUsers[i].playerType !== 'spectator' && socketMessage.connectedUsers[i].playerType === PLAYERCOLOR) {
                    playerFound = true;
                }

                // Not spectator and color is different than the player one, then, the opponent is connected
                if (socketMessage.connectedUsers[i].playerType !== 'spectator' && socketMessage.connectedUsers[i].playerType !== PLAYERCOLOR) {
                    opponentFound = true;
                }

                // Check the spectators number
                if (socketMessage.connectedUsers[i].playerType === 'spectator') {
                    spectatorsNumber++;
                }
            }

            // Update player connection status
            if (playerFound) {
                $('.player-connect').removeClass('user-disconnected');
                $('.player-connect').addClass('user-connected');
            } else {
                $('.player-connect').addClass('user-disconnected');
                $('.player-connect').removeClass('user-connected');
            }

            // update opponent status
            if (opponentFound) {
                $('.opponent-connect').removeClass('user-disconnected');
                $('.opponent-connect').addClass('user-connected');

                if ($('#begining-with-friend-modal').hasClass('show')) {
                    $('#begining-with-friend-modal').modal('hide');
                }

                if ($('#begining-random-modal').hasClass('show')) {
                    $('#begining-random-modal').modal('hide');
                }
            } else {
                $('.opponent-connect').addClass('user-disconnected');
                $('.opponent-connect').removeClass('user-connected');
            }

            // One or more spectators, remove d-none on div if there is one, and update the number
            if (spectatorsNumber > 0 && $('.spectators').hasClass('d-none')) {
                $('.spectators').removeClass('d-none');
            }

            if (spectatorsNumber === 0 && !$('.spectators').hasClass('d-none')){ // No spectators
                $('.spectators').addClass('d-none');
            }
            $('.spectators-number').html(spectatorsNumber);


            let fenSplit = chess.fen().split(' ');
            let fenTurnNumber = parseInt(fenSplit[5]);
            if (fenTurnNumber >= 2) { // Timer must have started and is not started
                if (fenSplit[1] === 'b') { // Black turn
                    if (PLAYERCOLOR === 'b') {
                        // Set up player timer
                        startTimer(false, 'player', socketMessage.blackMicrotimeSpend);
                    } else {
                        // Set up opponent timer
                        startTimer(false, 'opponent', socketMessage.blackMicrotimeSpend);
                    }
                } else { // White turn
                    if (PLAYERCOLOR === 'w') {
                        // Set up player timer
                        startTimer(false, 'player', socketMessage.whiteMicrotimeSpend);
                    } else {
                        // Set up opponent timer
                        startTimer(false, 'opponent', socketMessage.whiteMicrotimeSpend);
                    }
                }
            }
            return;
        }

        // Message in the tchat from the opponent
        if (typeof socketMessage.method !== 'undefined' && socketMessage.method === 'tchat-message') {
            $('.tchat').append('<div>' + socketMessage.message + '</div>');

            return;
        }

        // Opponent resign
        if (typeof socketMessage.method !== 'undefined' && socketMessage.method === 'resign') {
            let reasonMessage = 'Black win ! White resign';
            if (PLAYERCOLOR === 'w') {
                reasonMessage = 'White win ! Black resign';
            }
            gameIsOver('b', reasonMessage);
            return;
        }

        // Opponent propose a draw
        if (typeof socketMessage.method !== 'undefined' && socketMessage.method === 'offer-draw') {
            if (PLAYERTYPE !== 'spectator') {
                $('#offer-draw-opponent-response').removeClass('d-none');
            }
            $('.tchat').append('<div>' + socketMessage.message + '</div>');
            return;
        }

        // Opponent refuse the draw
        if (typeof socketMessage.method !== 'undefined' && socketMessage.method === 'offer-draw-no') {
            if (PLAYERTYPE !== 'spectator') {
                $('#offer-draw-display').addClass('d-none');
            }
            $('.tchat').append('<div>' + socketMessage.message + '</div>');
            return;
        }

        // Opponent accept the draw
        if (typeof socketMessage.method !== 'undefined' && socketMessage.method === 'offer-draw-yes') {
            if (PLAYERTYPE !== 'spectator') {
                $('#offer-draw-display').addClass('d-none');
            }
            gameIsOver(null, socketMessage.message);
            return;
        }

        // Opponent ask for a takeback
        if (typeof socketMessage.method !== 'undefined' && socketMessage.method === 'takeback') {
            if (PLAYERTYPE !== 'spectator') {
                $('#takeback-opponent-response').removeClass('d-none');
            }
            $('.tchat').append('<div>' + socketMessage.message + '</div>');
            return;
        }

        // Opponent refuses the takeback
        if (typeof socketMessage.method !== 'undefined' && socketMessage.method === 'takeback-no') {
            if (PLAYERTYPE !== 'spectator') {
                $('#takeback-display').addClass('d-none');
            }
            $('.tchat').append('<div>' + socketMessage.message + '</div>');
            return;
        }

        // Opponent accepts the takeback
        if (typeof socketMessage.method !== 'undefined' && socketMessage.method === 'takeback-yes') {
            if (PLAYERTYPE !== 'spectator') {
                $('#takeback-display').addClass('d-none');
            }
            $('.tchat').append('<div>' + socketMessage.message + '</div>');

            // If it's the PLAYERCOLOR turn, we need to remove 2 moves, the opponent turn and next the PLAYERCOLOR one
            // If it's NOT the PLAYERCOLOR turn, we need to remove only the PLAYERCOLOR turn
            if (chess.turn() === PLAYERCOLOR) {
                let undo = chess.undo();
                let fenSplit = undo.before.split(' ');
                let moveNumber = fenSplit[5];
                // If undo a white move, remove an history line
                if (undo.color === 'w') {
                    $('.move-' + moveNumber).remove();
                } else { // If undo a black move, empty the move history square
                    $('#move-san-b-' + moveNumber).empty();
                }
            } else { // If we need to remove only the PLAYERCOLOR turn, we have to switch turn
                switchTurn();
            }

            let undo = chess.undo();
            let fenSplit = undo.before.split(' ');
            let moveNumber = fenSplit[5];
            // If undo a white move, remove an history line
            if (undo.color === 'w') {
                $('.move-' + moveNumber).remove();
            } else { // If undo a black move, empty the move history square
                $('#move-san-b-' + moveNumber).empty();
            }

            placePieces(chess.fen(), false, chess);

            $('.in-check').removeClass('in-check');
            if (chess.inCheck() === true) {
                let kingposition = getKingPosition(chess.fen(), chess.turn());
                $('#' + kingposition).addClass('in-check');
            }

            $('.last-history-move').removeClass('last-history-move');

            calculateMaterial(chess.fen(), PLAYERCOLOR);

            let chessHistory = chess.history({verbose: true});
            // We need to use the before for this one
            if (typeof chessHistory[chessHistory.length - 1] !== 'undefined') {
                let fenSplitForHistory = (chessHistory[chessHistory.length - 1].before).split(' ');
                // fenSplitForHistory[1] is color and fenSplitForHistory[5] is the move number
                $('#move-san-' + fenSplitForHistory[1] + '-' + fenSplitForHistory[5]).addClass('last-history-move');
            }

            setupDraggable();

            return;
        }

        // Timer update, in case for exemple a takeback is accepted
        if (typeof socketMessage.method !== 'undefined' && socketMessage.method === 'update-timers') {
            if (PLAYERCOLOR === 'w') {
                remainingTimePlayer = socketMessage.white_time_left * 1000; // Player timeleft in milliseconds
                remainingTimeOpponent = socketMessage.black_time_left * 1000; // Player timeleft in milliseconds
            } else {
                remainingTimePlayer = socketMessage.black_time_left * 1000; // Player timeleft in milliseconds
                remainingTimeOpponent = socketMessage.white_time_left * 1000; // Player timeleft in milliseconds
            }

            // Update this variables to update both timers
            endTimePlayer = Date.now() + remainingTimePlayer;
            endTimeOpponent = Date.now() + remainingTimeOpponent;

            return;
        }

        // Opponent timer is over
        if (typeof socketMessage.method !== 'undefined' && socketMessage.method === 'timeout') {
            let reasonMessage = 'Black win ! White timer is over';
            if (PLAYERCOLOR === 'w') {
                reasonMessage = 'White win ! Black timer is over';
            }
            gameIsOver(socketMessage.colorWinner, reasonMessage);

            if (PLAYERCOLOR === 'w' && socketMessage.colorWinner === 'w') {
                $(".timer-opponent").text("00:00");
                $('.timer-top').removeClass('active');
                $('.timer-top').addClass('over');
            } else if (PLAYERCOLOR === 'b' && socketMessage.colorWinner === 'b') {
                $(".timer-opponent").text("00:00");
                $('.timer-top').removeClass('active');
                $('.timer-top').addClass('over');
            } else if (PLAYERCOLOR === 'w' && socketMessage.colorWinner === 'b') {
                $(".timer-player").text("00:00");
                $('.timer-bottom').removeClass('active');
                $('.timer-bottom').addClass('over');
            } else if (PLAYERCOLOR === 'b' && socketMessage.colorWinner === 'w') {
                $(".timer-player").text("00:00");
                $('.timer-bottom').removeClass('active');
                $('.timer-bottom').addClass('over');
            }

            return;
        }

        // Move
        // Display the move only if the player is not watching the history
        if (!HISTORYINVIEW) {
            if (socketMessage.flags === 'k') { // king side castelling
                if (socketMessage.color === 'w') {
                    let pieceImg = $('#h1').find('img');
                    $('#h1 img').remove();
                    $('#f1').append(pieceImg);
                    setupDraggable('#f1 img');
                } else if (socketMessage.color === 'b') {
                    let pieceImg = $('#h8').find('img');
                    $('#h8 img').remove();
                    $('#f8').append(pieceImg);
                    setupDraggable('#f8 img');
                }
            } else if (socketMessage.flags === 'q') { // queen side castelling
                if (socketMessage.color === 'w') {
                    let pieceImg = $('#a1').find('img');
                    $('#a1 img').remove();
                    $('#d1').append(pieceImg);
                    setupDraggable('#d1 img');
                } else if (socketMessage.color === 'b') {
                    let pieceImg = $('#a8').find('img');
                    $('#a8 img').remove();
                    $('#d8').append(pieceImg);
                    setupDraggable('#d8 img');
                }
            } else if (socketMessage.flags === 'e') { // en passant capture
                if (socketMessage.color === 'w') {
                    var tmp = (socketMessage.to).split('');
                    var idPawnCatured = tmp[0] + (parseInt(tmp[1]) - 1);
                } else if (socketMessage.color === 'b') {
                    var tmp = (socketMessage.to).split('');
                    var idPawnCatured = tmp[0] + (parseInt(tmp[1]) + 1);
                }
                if ($('#' + idPawnCatured).find('img').length > 0) {
                    $('#' + idPawnCatured + ' img').remove();
                }
            }
        }

        if (typeof socketMessage.promotion !== 'undefined' && socketMessage.promotion !== null) { // promotion
            var piecesPromotion = {
                'r': 'rook',
                'n': 'knight',
                'b': 'bishop',
                'q': 'queen',
            };

            // Display the move only if the player is not watching the history
            if (!HISTORYINVIEW) {
                let src = PIECESIMGURL;
                src = src.replace('chessboard', piecesPromotion[socketMessage.promotion]);
                src = src.replace('playerColor', socketMessage.color);
                if ($('#' + socketMessage.from).find('img').length > 0) {
                    $('#' + socketMessage.from + ' img').remove();
                }
                if ($('#' + socketMessage.to).find('img').length > 0) {
                    $('#' + socketMessage.to + ' img').remove();
                }
                $('#' + socketMessage.to).append('<img class="piece ' + socketMessage.color + '" src="' + src + '" alt>');
            }

            $('.in-check').removeClass('in-check');

            try {
                chess.move({
                    from: socketMessage.from,
                    to: socketMessage.to,
                    promotion: socketMessage.promotion,
                });
            } catch (error) {
                console.log(error);
            }

            if (chess.isGameOver()) {
                if (chess.isCheckmate() === true) {
                    let reasonMessage = 'Black win ! White is checkmate';
                    if (socketMessage.color === 'w') {
                        reasonMessage = 'White win ! Black is checkmate';
                    }
                    gameIsOver(socketMessage.color, reasonMessage);
                } else if (chess.isDraw()) {
                    let reasonMessage = 'Draw ! Fifty moves without progressions';
                    if (chess.isStalemate()) {
                        reasonMessage = 'Draw ! Game is stalemated';
                    } else if (chess.isThreefoldRepetition()) {
                        reasonMessage = 'Draw ! Threefold repetition';
                    } else if (chess.isInsufficientMaterial()) {
                        reasonMessage = 'Draw ! Insufficient material';
                    }
                    gameIsOver(null, reasonMessage);
                }
            }

            if (chess.inCheck() === true) {
                let kingposition = '';
                if (socketMessage.color === 'w') {
                    kingposition = getKingPosition(socketMessage.after, 'b');
                } else {
                    kingposition = getKingPosition(socketMessage.after, 'w');
                }
                $('#' + kingposition).addClass('in-check');
            }
            switchTurn();
        } else {
            // Display the move only if the player is not watching the history
            if (!HISTORYINVIEW) {
                // Square to has a piece, remove it
                if ($('#' + socketMessage.to).find('img').length > 0) {
                    $('#' + socketMessage.to + ' img').remove();
                }

                $('#' + socketMessage.from + ' img').detach().css({top: 0, left: 0}).appendTo('#' + socketMessage.to);
            }

            $('.in-check').removeClass('in-check');

            try {
                chess.move({
                    from: socketMessage.from,
                    to: socketMessage.to
                });
            } catch (error) {
                console.log(error);
            }

            if (chess.isGameOver()) {
                if (chess.isCheckmate() === true) {
                    let reasonMessage = 'Black win ! White is checkmate';
                    if (socketMessage.color === 'w') {
                        reasonMessage = 'White win ! Black is checkmate';
                    }
                    gameIsOver(socketMessage.color, reasonMessage);
                } else if (chess.isDraw()) {
                    let reasonMessage = 'Draw ! Fifty moves without progressions';
                    if (chess.isStalemate()) {
                        reasonMessage = 'Draw ! Game is stalemated';
                    } else if (chess.isThreefoldRepetition()) {
                        reasonMessage = 'Draw ! Threefold repetition';
                    } else if (chess.isInsufficientMaterial()) {
                        reasonMessage = 'Draw ! Insufficient material';
                    }
                    gameIsOver(null, reasonMessage);
                }
            }

            if (!HISTORYINVIEW) {
                if (chess.inCheck() === true) {
                    let kingposition = '';
                    if (socketMessage.color === 'w') {
                        kingposition = getKingPosition(socketMessage.after, 'b');
                    } else {
                        kingposition = getKingPosition(socketMessage.after, 'w');
                    }
                    $('#' + kingposition).addClass('in-check');
                }
            }

            if (typeof socketMessage.after !== 'undefined') {
                var tmp = socketMessage.after.split(' ');
                var tmp2 = parseInt(tmp[5]);
                if (tmp2 === 2 && socketMessage.color === 'b' && !isRunningPlayer && !isRunningOpponent) {
                    startTimer(false, 'player');
                } else {
                    if (PLAYERCOLOR === 'w') {
                        remainingTimePlayer = socketMessage.white_time_left * 1000; // Player timeleft in milliseconds
                        remainingTimeOpponent = socketMessage.black_time_left * 1000; // Player timeleft in milliseconds
                    } else {
                        remainingTimePlayer = socketMessage.black_time_left * 1000; // Player timeleft in milliseconds
                        remainingTimeOpponent = socketMessage.white_time_left * 1000; // Player timeleft in milliseconds
                    }

                    switchTurn();
                }
            }
        }

        calculateMaterial(socketMessage.after, PLAYERCOLOR);

        $('#title').html('C\'est votre tour ! | ' + SERVERNAME);

        $('.history-section-before').addClass('d-none');
        $('.history-section').removeClass('d-none');

        // Not history in view, manage the last-history-move
        if (!HISTORYINVIEW) {
            $('.history-section').find('.last-history-move').removeClass('last-history-move');
            if (socketMessage.color === 'w') { // White play, make a new line
                let htmlMoveRow = '' +
                '<div class="row move-' + socketMessage.moveNumber + ' text-center">' +
                    '<div class="col-3 move-index">' + socketMessage.moveNumber + '</div>' +
                    '<div id="move-san-w-' + socketMessage.moveNumber + '" class="col-4 one-move-san last-history-move">' + socketMessage.san + '</div>' +
                    '<div id="move-san-b-' + socketMessage.moveNumber + '" class="col-5 one-move-san"></div>' +
                '</div>';

                $('.history-section').append(htmlMoveRow);
            } else { // Black play, complete the line
                $('.history-section').find('#move-san-b-' + socketMessage.moveNumber).html(socketMessage.san);
                $('.history-section').find('#move-san-b-' + socketMessage.moveNumber).addClass('last-history-move');
            }
        } else { // If history in move, do not manage last-history-move
            if (socketMessage.color === 'w') { // White play, make a new line
                let htmlMoveRow = '' +
                '<div class="row move-' + socketMessage.moveNumber + ' text-center">' +
                    '<div class="col-3 move-index">' + socketMessage.moveNumber + '</div>' +
                    '<div id="move-san-w-' + socketMessage.moveNumber + '" class="col-4 one-move-san">' + socketMessage.san + '</div>' +
                    '<div id="move-san-b-' + socketMessage.moveNumber + '" class="col-5 one-move-san"></div>' +
                '</div>';

                $('.history-section').append(htmlMoveRow);
            } else { // Black play, complete the line
                $('.history-section').find('#move-san-b-' + socketMessage.moveNumber).html(socketMessage.san);
            }
        }

        if (!HISTORYINVIEW) {
            $('.chess-table.last-move').each(function() {
                $(this).removeClass('last-move');
            });

            $('#' + socketMessage.from).addClass('last-move');
            $('#' + socketMessage.to).addClass('last-move');
        }

        // 2 squares premove, execute the move instantly
        if ($('.clicked-premove').length === 2) {
            let squareIdFrom = null;
            let squareIdTo = null;
            $('.clicked-premove').each(function() {
                // date.premove == 1 then the move start from this one, get the id of the square
                if ($(this).data('premove') == 1) {
                    squareIdFrom = $(this).attr('id');
                }

                // date.premove == 2 then the move goes to this one, get the id of the square
                if ($(this).data('premove') == 2) {
                    squareIdTo = $(this).attr('id');
                }
            });

            if (squareIdFrom !== null && squareIdTo !== null) {
                let squareFromInfos = chess.get(squareIdFrom);

                let tmp2 = squareIdFrom.split('');
                let idFromLine = parseInt(tmp2[1]);

                let tmp3 = squareIdTo.split('');
                let idToLine = parseInt(tmp3[1]);

                let promotion = null;
                if (squareFromInfos !== null && squareFromInfos['type'] === 'p' && ((PLAYERCOLOR === 'w' && squareFromInfos['color'] === 'w' && idFromLine === 7 && idToLine === 8) || (PLAYERCOLOR === 'b' && squareFromInfos['color'] === 'b' && idFromLine === 2 && idToLine === 1))) {
                    promotionPiece(function(promotion) {
                        processMove(squareIdFrom, squareIdTo, promotion);
                    });
                } else {
                    processMove(squareIdFrom, squareIdTo, promotion);
                }
            }
        }
    });

    socket.addEventListener('close', function(e) {
        console.log('close', e);
    });

    socket.addEventListener('error', function(e) {
        console.log('error', e);
    });

    $('#copy-game-link').on('click', function() {
        navigator.clipboard.writeText(GAMEAWAITURL).then(function() {
            $('#copy-game-link').html('<i class="bi bi-check-lg"></i>');
        }).catch(function(error) { // If copy fails, should never happen but we never know ...
            $('#copy-game-link').html('<i class="bi bi-x-lg"></i>');
            console.error('Copy error :', error);
        });
    })

    $('#board').off().on('click', '.chess-table', function() {
        // If player is watching history, disable the possibility to move
        if (HISTORYINVIEW) {
            return;
        }

        // If game is finished, disable moves
        if (GAMESTATUS === 'finished') {
            return;
        }

        // If the user is a spectator, disable moves
        if (PLAYERTYPE === 'spectator') {
            return;
        }

        const self = $(this);
        var idSquare = $(this).attr('id');

        // If all squares has the class clicked-premove-hover because a premove was made before, remove it
        $('.chess-table').each(function() {
            $(this).removeClass('clicked-premove-hover');
        });

        // If 2 squares are already premoved, remove them
        if ($('.chess-table.clicked-premove').length === 2) {
            $('.chess-table.clicked-premove').removeClass('clicked-premove');
            return;
        }

        // Select one of the player piece
        if ($(this).find('.piece.' + PLAYERCOLOR).length === 1) {
            let playerTurn = chess.turn(); // Which player turn it is
            if (
                (PLAYERCOLOR === 'w' && playerTurn === 'b') ||
                (PLAYERCOLOR === 'b' && playerTurn === 'w')
            ) { // If not the player turn, then make a premove
                if ($(this).hasClass('clicked-premove')) {
                    $('.chess-table.clicked-premove').removeClass('clicked-premove');
                    return;
                }

                if ($('.chess-table.clicked-premove').length === 0) { // No clicked-premove square, first click
                    $(this).addClass('clicked-premove');
                    $(this).data('premove', 1);
                    $('.chess-table').each(function() {
                        $(this).addClass('clicked-premove-hover');
                    });
                } else { // If another case is clicked-premove, then it is the second one
                    $(this).addClass('clicked-premove');
                    $(this).data('premove', 2);
                }
            } else {
                var samePieceClicked = false;
                if ($(this).hasClass('clicked')) {
                    var samePieceClicked = true;
                }

                // If another case is clicked, remove it
                $('.chess-table.clicked').removeClass('clicked');
                $('.chess-table.possible-move').each(function() {
                    $(this).removeClass('possible-move');
                });

                // Same square clicked, then it removes the clicked class and do nothing else
                if (samePieceClicked) {
                    return;
                }

                $(this).addClass('clicked');

                var allPossibleMoves = chess.moves({
                    verbose: true
                });

                for (var i in allPossibleMoves) {
                    if (allPossibleMoves[i].from === idSquare) {
                        $('#' + allPossibleMoves[i].to).addClass('possible-move');
                    }
                }
            }
        }

        // Click on a square with "possible-move" class and #board has 1 element "clicked" then proceed a move
        if ($(this).hasClass('possible-move') && $('#board').find('.clicked').length === 1) {
            var squareIdFrom = $('#board').find('.clicked').attr('id');
            var squareIdTo = idSquare;

            var squareFromInfos = chess.get(squareIdFrom);

            var tmp2 = squareIdFrom.split('');
            var idFromLine = parseInt(tmp2[1]);

            var tmp3 = squareIdTo.split('');
            var idToLine = parseInt(tmp3[1]);

            var promotion = null;
            if (squareFromInfos !== null && squareFromInfos['type'] === 'p' && ((PLAYERCOLOR === 'w' && squareFromInfos['color'] === 'w' && idFromLine === 7 && idToLine === 8) || (PLAYERCOLOR === 'b' && squareFromInfos['color'] === 'b' && idFromLine === 2 && idToLine === 1))) {
                promotionPiece(function(promotion) {
                    processMove(squareIdFrom, squareIdTo, promotion);
                });
            } else {
                processMove(squareIdFrom, squareIdTo, promotion);
            }
        }

        // Second click to premove in no piece square
        if ($('#board').find('.clicked-premove').length === 1 && !$(self).hasClass('clicked-premove')) {
            $(self).addClass('clicked-premove');
            $(self).data('premove', 2);
        }
    });

    // Set up draggable only if the game is not finished and the player is not a spectator
    if (GAMESTATUS !== 'finished' && PLAYERTYPE !== 'spectator') {
        setupDraggable();
    }

    $('.chess-table').droppable({
        drop: function(ev, ui) {
            // If game is finished, disable moves
            if (GAMESTATUS === 'finished') {
                return;
            }

            // If the user is a spectator, disable moves
            if (PLAYERTYPE === 'spectator') {
                return;
            }

            // If player is watching history, disable the possibility to move
            if (HISTORYINVIEW) {
                return;
            }

            let playerTurn = chess.turn(); // Which player turn it is
            if (
                (PLAYERCOLOR === 'w' && playerTurn === 'b') ||
                (PLAYERCOLOR === 'b' && playerTurn === 'w')
            ) { // If not the player turn, then make a premove
                $(this).addClass('clicked-premove');
                $(this).data('premove', 2);
            } else {
                var squareIdFrom = (ui.draggable).parent().attr('id');
                var squareIdTo = $(this).attr('id');

                var squareFromInfos = chess.get(squareIdFrom);

                var tmp2 = squareIdFrom.split('');
                var idFromLine = parseInt(tmp2[1]);

                var tmp3 = squareIdTo.split('');
                var idToLine = parseInt(tmp3[1]);

                var promotion = null;
                if (squareFromInfos !== null && squareFromInfos['type'] === 'p' && ((PLAYERCOLOR === 'w' && squareFromInfos['color'] === 'w' && idFromLine === 7 && idToLine === 8) || (PLAYERCOLOR === 'b' && squareFromInfos['color'] === 'b' && idFromLine === 2 && idToLine === 1))) {
                    promotionPiece(function(promotion) {
                        processMove(squareIdFrom, squareIdTo, promotion);
                    });
                } else {
                    processMove(squareIdFrom, squareIdTo, promotion);
                }
            }
        }
    });

    $('.history-button').on('click', function() {
        let self = $(this);

        $('.chess-table.last-move').each(function() {
            $(this).removeClass('last-move');
        });

        $('.last-history-move').removeClass('last-history-move');

        // in-check class will be recalculate for each history except the start button
        $('.in-check').removeClass('in-check');

        let chessHistory = chess.history({verbose: true});
        if (self.attr('id') === 'history-start') { // Go to the begening of the game
            HISTORYINVIEW = true;
            HISTORYINDEX = -1;
            placePieces(chessHistory[0].before, true);
        } else if (self.attr('id') === 'history-end') { // Go to the end of the game
            HISTORYINVIEW = false;

            HISTORYINDEX = chessHistory.length - 1;
            placePieces(chessHistory[chessHistory.length - 1].after, true);

            $('#' + chessHistory[chessHistory.length - 1].from).addClass('last-move');
            $('#' + chessHistory[chessHistory.length - 1].to).addClass('last-move');

            // We need to use the before for this one
            let fenSplit = (chessHistory[chessHistory.length - 1].before).split(' ');
            // fenSplit[1] is color and fenSplit[5] is the move number
            $('#move-san-' + fenSplit[1] + '-' + fenSplit[5]).addClass('last-history-move');

            // if san ends with '+', then the other color player is in check, display the class. If ends with '#', it's checkmate but apply the color too
            if ((chessHistory[chessHistory.length - 1].san).endsWith('+') || (chessHistory[chessHistory.length - 1].san).endsWith('#')) {
                let kingposition = '';
                if (chessHistory[chessHistory.length - 1].color === 'w') {
                    kingposition = getKingPosition(chessHistory[chessHistory.length - 1].after, 'b');
                } else {
                    kingposition = getKingPosition(chessHistory[chessHistory.length - 1].after, 'w');
                }
                $('#' + kingposition).addClass('in-check');
            }

            // Set draggable back on all player color pieces
            setupDraggable();
        } else if (self.attr('id') === 'history-backward') { // 1 step backward
            HISTORYINVIEW = true;
            if (HISTORYINDEX === null) {
                HISTORYINDEX = chessHistory.length - 2;
            } else if (HISTORYINDEX > -1) {
                HISTORYINDEX--;
            }

            if (typeof chessHistory[HISTORYINDEX] === 'undefined' && HISTORYINDEX !== -1) {
                return;
            }

            let fen = null;
            if (HISTORYINDEX === -1) {
                fen = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';
            } else {
                fen = chessHistory[HISTORYINDEX].after;
                $('#' + chessHistory[HISTORYINDEX].from).addClass('last-move');
                $('#' + chessHistory[HISTORYINDEX].to).addClass('last-move');

                // We need to use the before for this one
                let fenSplit = (chessHistory[HISTORYINDEX].before).split(' ');
                // fenSplit[1] is color and fenSplit[5] is the move number
                $('#move-san-' + fenSplit[1] + '-' + fenSplit[5]).addClass('last-history-move');

                // if san ends with '+', then the other color player is in check, display the class. If ends with '#', it's checkmate but apply the color too
                if ((chessHistory[HISTORYINDEX].san).endsWith('+') || (chessHistory[HISTORYINDEX].san).endsWith('#')) {
                    let kingposition = '';
                    if (chessHistory[HISTORYINDEX].color === 'w') {
                        kingposition = getKingPosition(chessHistory[HISTORYINDEX].after, 'b');
                    } else {
                        kingposition = getKingPosition(chessHistory[HISTORYINDEX].after, 'w');
                    }
                    $('#' + kingposition).addClass('in-check');
                }
            }

            placePieces(fen, true);
        } else if (self.attr('id') === 'history-forward') { // 1 step forward
            HISTORYINVIEW = true;
            if (HISTORYINDEX === null) {
                HISTORYINDEX = chessHistory.length - 1;
            } else if (typeof chessHistory[HISTORYINDEX + 1] !== 'undefined') {
                HISTORYINDEX++;
            }

            if (typeof chessHistory[HISTORYINDEX] === 'undefined') {
                return;
            }

            // Last move, history not in view
            if (HISTORYINDEX === chessHistory.length - 1) {
                HISTORYINVIEW = false;
            }

            $('#' + chessHistory[HISTORYINDEX].from).addClass('last-move');
            $('#' + chessHistory[HISTORYINDEX].to).addClass('last-move');

            // We need to use the before for this one
            let fenSplit = (chessHistory[HISTORYINDEX].before).split(' ');
            // fenSplit[1] is color and fenSplit[5] is the move number
            $('#move-san-' + fenSplit[1] + '-' + fenSplit[5]).addClass('last-history-move');

            // if san ends with '+', then the other color player is in check, display the class.  If ends with '#', it's checkmate but apply the color too
            if ((chessHistory[HISTORYINDEX].san).endsWith('+') || (chessHistory[HISTORYINDEX].san).endsWith('#')) {
                let kingposition = '';
                if (chessHistory[HISTORYINDEX].color === 'w') {
                    kingposition = getKingPosition(chessHistory[HISTORYINDEX].after, 'b');
                } else {
                    kingposition = getKingPosition(chessHistory[HISTORYINDEX].after, 'w');
                }
                $('#' + kingposition).addClass('in-check');
            }

            placePieces(chessHistory[HISTORYINDEX].after, true);

            // Set draggable back on all player color pieces if we set the last move
            if (HISTORYINDEX === chessHistory.length - 1) {
                setupDraggable();
            }
        }
    });

    $('.history-section').on('click', '.one-move-san', function() {
        // Empty history line, just return
        if ($(this).html() === '') {
            return;
        }

        $('.chess-table.last-move').each(function() {
            $(this).removeClass('last-move');
        });

        $('.last-history-move').removeClass('last-history-move');

        // in-check class will be recalculate for each history except the start button
        $('.in-check').removeClass('in-check');

        let chessHistory = chess.history({verbose: true});

        let elementId = $(this).attr('id');
        let elementIdSplit = elementId.split('-');
        let elementColorTurn = elementIdSplit[2];
        let elementMoveNumber = elementIdSplit[3];

        for (let i in chessHistory) {
            i = parseInt(i); // variable i is string, so parse it

            let historyFenSplit = (chessHistory[i].before).split(' ');
            let hisotryColorTurn = historyFenSplit[1];
            let historyMoveNumber = historyFenSplit[5];


            if (hisotryColorTurn === elementColorTurn && elementMoveNumber === historyMoveNumber) {
                HISTORYINDEX = i;

                // Click on the last history, it's the last move
                if (HISTORYINDEX === chessHistory.length - 1) {
                    HISTORYINVIEW = false;
                } else {
                    HISTORYINVIEW = true;
                }

                $('#' + chessHistory[i].from).addClass('last-move');
                $('#' + chessHistory[i].to).addClass('last-move');

                // We need to use the before for this one
                let fenSplit = (chessHistory[i].before).split(' ');
                // fenSplit[1] is color and fenSplit[5] is the move number
                $('#move-san-' + fenSplit[1] + '-' + fenSplit[5]).addClass('last-history-move');

                // if san ends with '+', then the other color player is in check, display the class. If ends with '#', it's checkmate but apply the color too
                if ((chessHistory[i].san).endsWith('+') || (chessHistory[i].san).endsWith('#')) {
                    let kingposition = null;
                    if (chessHistory[i].color === 'w') {
                        kingposition = getKingPosition(chessHistory[i].after, 'b');
                    } else {
                        kingposition = getKingPosition(chessHistory[i].after, 'w');
                    }
                    $('#' + kingposition).addClass('in-check');
                }

                placePieces(chessHistory[i].after, true);

                if (HISTORYINDEX === chessHistory.length - 1) {
                    setupDraggable();
                }
            }
        }
    });

    $('#resign').on('click', function() {
        // If game is finished, disable resigns
        if (GAMESTATUS === 'finished') {
            return;
        }

        // If the user is a spectator, disable resigns
        if (PLAYERTYPE === 'spectator') {
            return;
        }

        $('#resign-modal').modal('show');
        $('#confirm-resign-modal').off().on('click', function() {
            $('#resign-modal').modal('hide');

            let reasonMessage = 'White win ! Black resign';
            if (PLAYERCOLOR === 'w') {
                reasonMessage = 'Black win ! White resign';
            }
            gameIsOver(PLAYERCOLOR, reasonMessage);

            try {
                socket.send(JSON.stringify({
                    'method': 'resign',
                    'idGame': IDGAME,
                    'color': PLAYERCOLOR,
                }));
            } catch (error) {
                console.log('Socket error', error);
            }
        });
    });

    $('#offer-draw').on('click', function() {
        // If game is finished, disable draw offers
        if (GAMESTATUS === 'finished') {
            return;
        }

        // If the user is a spectator, disable draw offers
        if (PLAYERTYPE === 'spectator') {
            return;
        }

        $('#offerdraw-modal').modal('show');
        $('#confirm-offerdraw-modal').off().on('click', function() {
            $('#offerdraw-modal').modal('hide');

            $('#offer-draw-display').removeClass('d-none');

            let message = 'Black offers draw';
            if (PLAYERCOLOR === 'w') {
                message = 'White offers draw';
            }

            $('.tchat').append('<div>' + message + '</div>');

            try {
                socket.send(JSON.stringify({
                    'method': 'offer-draw',
                    'idGame': IDGAME,
                    'message': message,
                }));
            } catch (error) {
                console.log('Socket error', error);
            }
        });
    });

    $('#offer-draw-yes').on('click', function() {
        $('#offer-draw-opponent-response').addClass('d-none');

        let message = 'Draw offer accepted';
        gameIsOver(null, message);

        try {
            socket.send(JSON.stringify({
                'method': 'offer-draw-yes',
                'idGame': IDGAME,
                'message': message,
            }));
        } catch (error) {
            console.log('Socket error', error);
        }
    });
    $('#offer-draw-no').on('click', function() {
        $('#offer-draw-opponent-response').addClass('d-none');

        let message = 'Black declines draw';
        if (PLAYERCOLOR === 'w') {
            message = 'White declines draw';
        }

        $('.tchat').append('<div>' + message + '</div>');

        try {
            socket.send(JSON.stringify({
                'method': 'offer-draw-no',
                'idGame': IDGAME,
                'message': message,
            }));
        } catch (error) {
            console.log('Socket error', error);
        }
    });

    $('#takeback').on('click', function() {
        // If game is finished, disable takeback
        if (GAMESTATUS === 'finished') {
            return;
        }

        // If the user is a spectator, disable takeback
        if (PLAYERTYPE === 'spectator') {
            return;
        }

        // Disable takeback in the first move
        if (chess.moveNumber() === 1) {
            return;
        }

        $('#takeback-display').removeClass('d-none');

        let message = 'Takeback sent';
        $('.tchat').append('<div>' + message + '</div>');

        try {
            socket.send(JSON.stringify({
                'method': 'takeback',
                'idGame': IDGAME,
                'message': message,
                'noSave': true, // Don't save in DB this message
            }));
        } catch (error) {
            console.log('Socket error', error);
        }
    });
    $('#takeback-no').on('click', function() {
        $('#takeback-opponent-response').addClass('d-none');

        let message = 'Takeback declined';
        $('.tchat').append('<div>' + message + '</div>');
        try {
            socket.send(JSON.stringify({
                'method': 'takeback-no',
                'idGame': IDGAME,
                'message': message,
                'noSave': true, // Don't save in DB this message
            }));
        } catch (error) {
            console.log('Socket error', error);
        }
    });
    $('#takeback-yes').on('click', function() {
        $('#takeback-opponent-response').addClass('d-none');

        let message = 'Takeback accepted';
        $('.tchat').append('<div>' + message + '</div>');

        // If it's the PLAYERCOLOR turn, we need to remove only one move, the opponent one
        // If it's NOT the PLAYERCOLOR turn, we need to remove 2 moves, the PLAYERCOLOR turn and next the opponent one
        let onlyOne = true;
        if (chess.turn() !== PLAYERCOLOR) {
            let undo = chess.undo();
            let fenSplit = undo.before.split(' ');
            let moveNumber = fenSplit[5];
            // If undo a white move, remove an history line
            if (undo.color === 'w') {
                $('.move-' + moveNumber).remove();
            } else { // If undo a black move, empty the move history square
                $('#move-san-b-' + moveNumber).empty();
            }
            onlyOne = false;
        } else {
            switchTurn();
        }

        let undo = chess.undo();
        let fenSplit = undo.before.split(' ');
        let moveNumber = fenSplit[5];
        // If undo a white move, remove an history line
        if (undo.color === 'w') {
            $('.move-' + moveNumber).remove();
        } else { // If undo a black move, empty the move history square
            $('#move-san-b-' + moveNumber).empty();
        }

        placePieces(chess.fen(), false, chess);

        $('.in-check').removeClass('in-check');
        if (chess.inCheck() === true) {
            let kingposition = getKingPosition(chess.fen(), chess.turn());
            $('#' + kingposition).addClass('in-check');
        }

        $('.last-history-move').removeClass('last-history-move');

        let chessHistory = chess.history({verbose: true});
        // We need to use the before for this one
        if (typeof chessHistory[chessHistory.length - 1] !== 'undefined') {
            let fenSplitForHistory = (chessHistory[chessHistory.length - 1].before).split(' ');
            // fenSplitForHistory[1] is color and fenSplitForHistory[5] is the move number
            $('#move-san-' + fenSplitForHistory[1] + '-' + fenSplitForHistory[5]).addClass('last-history-move');
        }

        calculateMaterial(chess.fen(), PLAYERCOLOR);

        setupDraggable();

        try {
            socket.send(JSON.stringify({
                'method': 'takeback-yes',
                'idGame': IDGAME,
                'color': PLAYERCOLOR,
                'message': message,
                'fen' : chess.fen(),
                'pgn': chess.pgn(),
                'onlyOne': onlyOne,
            }));
        } catch (error) {
            console.log('Socket error', error);
        }
    });

    $('#form-tchat').on('submit', function() {
        if ($('#input-tchat').val() === '') {
            return false; // empty message, do nothing
        }

        let message = '[white] ';
        if (PLAYERCOLOR === 'b') {
            message = '[black] ';
        }
        message += $('#input-tchat').val();

        socket.send(JSON.stringify({
            'method': 'tchat-message',
            'idGame': IDGAME,
            'message': message,
            'color': PLAYERCOLOR,
        }));

        $('#input-tchat').val('');
        $('.tchat').append('<div>' + message + '</div>');

        return false; // Return false to prevent a real submit of the form and a reload of the page
    });
});

function setupDraggable(jQueryElement) {
    let elementToDraggable = '.piece.' + PLAYERCOLOR;
    if (typeof jQueryElement !== 'undefined') {
        elementToDraggable = jQueryElement;
    }

    $(elementToDraggable).draggable({
        revert: true,
        start: function(ev, ui) {
            // If player is watching history, disable the possibility to move
            if (HISTORYINVIEW) {
                return;
            }

            // If game is finished, disable moves
            if (GAMESTATUS === 'finished') {
                return;
            }

            // If the user is a spectator, disable moves
            if (PLAYERTYPE === 'spectator') {
                return;
            }

            // Remove the previous clicked class and possible-move class from all squares
            $('.chess-table.clicked').removeClass('clicked');
            $('.chess-table.possible-move').each(function() {
                $(this).removeClass('possible-move');
            });

            $('.chess-table.clicked-premove').removeClass('clicked-premove');
            const self = $(this);
            let playerTurn = chess.turn(); // Which player turn it is
            if (
                (PLAYERCOLOR === 'w' && playerTurn === 'b') ||
                (PLAYERCOLOR === 'b' && playerTurn === 'w')
            ) { // If not the player turn, then make a premove
                $(self).parent().addClass('clicked-premove');
                $(self).parent().data('premove', 1);
            } else {
                $(self).parent().addClass('clicked');

                var allPossibleMoves = chess.moves({
                    verbose: true
                });

                for (var i in allPossibleMoves) {
                    if (allPossibleMoves[i].from === $(self).parent().attr('id')) {
                        $('#' + allPossibleMoves[i].to).addClass('possible-move');
                    }
                }
            }
        },
        stop: function(ev, ui) {
            const targetElement = $(document.elementFromPoint(
                ev.clientX,
                ev.clientY
            ));

            // If target element is undefined, it's the same square
            // In that case, we don't remove the clicked stuff to make the UI for firendly
            if (typeof targetElement.attr('id') !== 'undefined') {
                $('.chess-table.clicked').removeClass('clicked');
                $('.chess-table.possible-move').each(function() {
                    $(this).removeClass('possible-move');
                });
            }
        }
    });
}

function processMove(squareIdFrom, squareIdTo, promotion) {
    HISTORYINDEX = null;

    var piecesPromotion = {
        'r': 'rook',
        'n': 'knight',
        'b': 'bishop',
        'q': 'queen',
    };

    var wasInCheck = chess.inCheck();
    var moving = null;

    var moveNumber = chess.moveNumber();

    try {
        moving = chess.move({
            from: squareIdFrom,
            to: squareIdTo,
            promotion: promotion,
        });
    } catch (error) {
        let regex = /Invalid move/;
        if (!regex.test(error)) { // Do not display a log if it's an invalid move
            console.log(error);
        }
    }

    if (moving !== null) {
        if (chess.inCheck() === true) {
            let kingPosition = '';
            if (moving.color === 'w') {
                kingPosition = getKingPosition(chess.fen(), 'b');
            } else {
                kingPosition = getKingPosition(chess.fen(), 'w');
            }
            $('#' + kingPosition).addClass('in-check');
        }

        if (wasInCheck) {
            $('.in-check').removeClass('in-check');
        }
        if (moving.flags === 'k') { // king side castelling
            if (PLAYERCOLOR === 'w') {
                let pieceImg = $('#h1').find('img');
                $('#h1 img').remove();
                $('#f1').append(pieceImg);
                setupDraggable('#f1 img');
            } else if (PLAYERCOLOR === 'b') {
                let pieceImg = $('#h8').find('img');
                $('#h8 img').remove();
                $('#f8').append(pieceImg);
                setupDraggable('#f8 img');
            }
        } else if (moving.flags === 'q') { // queen side castelling
            if (PLAYERCOLOR === 'w') {
                let pieceImg = $('#a1').find('img');
                $('#a1 img').remove();
                $('#d1').append(pieceImg);
                setupDraggable('#d1 img');
            } else if (PLAYERCOLOR === 'b') {
                let pieceImg = $('#a8').find('img');
                $('#a8 img').remove();
                $('#d8').append(pieceImg);
                setupDraggable('#d8 img');
            }
        } else if (moving.flags === 'e') { // en passant capture
            if (PLAYERCOLOR === 'w') {
                var tmp = (moving.to).split('');
                var idPawnCatured = tmp[0] + (parseInt(tmp[1]) - 1);
            } else if (PLAYERCOLOR === 'b') {
                var tmp = (moving.to).split('');
                var idPawnCatured = tmp[0] + (parseInt(tmp[1]) + 1);
            }
            if ($('#' + idPawnCatured).find('img').length > 0) {
                $('#' + idPawnCatured + ' img').remove();
            }
        }

        if ($('#' + squareIdTo).find('img').length > 0) {
            $('#' + squareIdTo + ' img').remove();
        }

        if (promotion !== null) {
            if ($('#' + squareIdFrom).find('img').length > 0) {
                $('#' + squareIdFrom + ' img').remove();
            }

            let src = PIECESIMGURL;
            src = src.replace('chessboard', piecesPromotion[promotion]);
            src = src.replace('playerColor', PLAYERCOLOR);
            $('#' + squareIdTo).append('<img class="piece ' + PLAYERCOLOR + '" src="' + src + '" alt>');
            setupDraggable('#' + squareIdTo + ' img');
        } else {
            // css top and left are set because when dropping piece, a strange thing happen
            $('#' + squareIdFrom + ' img').detach().css({top: 0, left: 0}).appendTo('#' + squareIdTo);
        }

        let historyVerbose = chess.history({verbose: true});
        let lastMoveHistory = historyVerbose[historyVerbose.length - 1];
        lastMoveHistory['idGame'] = IDGAME;
        lastMoveHistory['method'] = 'move';
        lastMoveHistory['moveNumber'] = moveNumber;
        lastMoveHistory['pgn'] = chess.pgn();
        lastMoveHistory['timersStarted'] = false;
        if (isRunningPlayer || isRunningOpponent) {
            lastMoveHistory['timersStarted'] = true;
        }

        $('.history-section-before').addClass('d-none');
        $('.history-section').removeClass('d-none');

        $('.history-section').find('.last-history-move').removeClass('last-history-move');
        if (moving.color === 'w') { // White play, make a new line
            let htmlMoveRow = '' +
            '<div class="row move-' + lastMoveHistory.moveNumber + ' text-center">' +
                '<div class="col-3 move-index">' + lastMoveHistory.moveNumber + '</div>' +
                '<div id="move-san-w-' + lastMoveHistory.moveNumber + '" class="col-4 one-move-san last-history-move">' + lastMoveHistory.san + '</div>' +
                '<div id="move-san-b-' + lastMoveHistory.moveNumber + '" class="col-5 one-move-san"></div>' +
            '</div>';

            $('.history-section').append(htmlMoveRow);
        } else { // Black play, complete the line
            $('.history-section').find('#move-san-b-' + lastMoveHistory.moveNumber).html(lastMoveHistory.san);
            $('.history-section').find('#move-san-b-' + lastMoveHistory.moveNumber).addClass('last-history-move');
        }

        if (chess.isGameOver()) {
            if (chess.isCheckmate() === true) {
                lastMoveHistory['gameStatus'] = 'checkmate';
                lastMoveHistory['gameReason'] = 'checkmate';
                if (moving.color === 'w') {
                    lastMoveHistory['message'] = 'White win ! Black is checkmate';
                } else {
                    lastMoveHistory['message'] = 'Black win ! White is checkmate';
                }
                gameIsOver(moving.color, lastMoveHistory['message']);
            } else if (chess.isDraw()) {
                lastMoveHistory['gameStatus'] = 'draw';
                if (chess.isStalemate()) {
                    lastMoveHistory['gameReason'] = 'stalemate';
                    lastMoveHistory['message'] = 'Draw ! Game is stalemated';
                } else if (chess.isThreefoldRepetition()) {
                    lastMoveHistory['gameReason'] = 'threefoldRepetition';
                    lastMoveHistory['message'] = 'Draw ! Threefold repetition';
                } else if (chess.isInsufficientMaterial()) {
                    lastMoveHistory['gameReason'] = 'insufficientMaterial';
                    lastMoveHistory['message'] = 'Draw ! Insufficient material';
                } else {
                    lastMoveHistory['gameReason'] = 'fiftyMoves';
                    lastMoveHistory['message'] = 'Draw ! Fifty moves without progressions';
                }
                gameIsOver(null, lastMoveHistory['message']);
            }
        }

        try {
            socket.send(JSON.stringify(lastMoveHistory));
        } catch (error) {
            console.log('Socket error', error);
        }

        var tmp = chess.fen().split(' ');
        var tmp2 = parseInt(tmp[5]);
        if (tmp2 === 2 && moving.color === 'b' && !isRunningPlayer && !isRunningOpponent) {
            startTimer(false, 'opponent');
        } else {
            switchTurn();
        }

        $('.chess-table.clicked').removeClass('clicked');
        $('.chess-table.possible-move').each(function() {
            $(this).removeClass('possible-move');
        });

        $('.chess-table.last-move').each(function() {
            $(this).removeClass('last-move');
        });
        $('.chess-table.clicked-premove').each(function() {
            $(this).removeClass('clicked-premove');
        });
        $('#' + squareIdFrom).addClass('last-move');
        $('#' + squareIdTo).addClass('last-move');

        $('#title').html('En attente de l\'adversaire | ' + SERVERNAME);

        calculateMaterial(lastMoveHistory.after, PLAYERCOLOR);

        return true;
    }

    $('.chess-table.clicked-premove').each(function() {
        $(this).removeClass('clicked-premove');
    });

    return false;
}

//
// timer functions
//

// Format milliseconds int to MM:SS
function formatTime(ms) {
    const totalSeconds = Math.max(0, Math.floor(ms / 1000)); // Évite les nombres négatifs
    const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
    const seconds = String(totalSeconds % 60).padStart(2, '0');
    return minutes + ':' + seconds;
}

// Update player timer
function updateTimerPlayer() {
    const currentTime = Date.now();
    remainingTimePlayer = endTimePlayer - currentTime; // Time left

    if (remainingTimePlayer <= 0) {
        $(".timer-player").text("00:00");

        // PLAYERCOLOR timer is over. The winner is the opponent
        if (PLAYERCOLOR === 'w') {
            gameIsOver('b', 'Black win ! White timer is over', 'timeout');
        } else if (PLAYERCOLOR === 'b') {
            gameIsOver('w', 'White win ! Black timer is over', 'timeout');
        }

        $('.timer-bottom').removeClass('active');
        $('.timer-bottom').addClass('over');
    } else {
        $(".timer-player").text(formatTime(remainingTimePlayer));
        $(".timer-opponent").text(formatTime(remainingTimeOpponent));
    }
}

// Update opponent timer
function updateTimerOpponent() {
    const currentTime = Date.now();
    remainingTimeOpponent = endTimeOpponent - currentTime; // Time left

    if (remainingTimeOpponent <= 0) {
        $(".timer-opponent").text("00:00");

        let reasonMessage = 'Black win ! White timer is over';
        if (PLAYERCOLOR === 'w') {
            reasonMessage = 'White win ! Black timer is over';
        }
        // Opponent timer is over. The winner is the Player
        gameIsOver(PLAYERCOLOR, reasonMessage, 'timeout');

        $('.timer-top').removeClass('active');
        $('.timer-top').addClass('over');
    } else {
        $(".timer-player").text(formatTime(remainingTimePlayer));
        $(".timer-opponent").text(formatTime(remainingTimeOpponent));
    }
}

// Loop autocalled to update timers
function loop() {
    if (isRunningPlayer) {
        updateTimerPlayer();
        animationFrame = requestAnimationFrame(loop); // Keeping the update
    } else if (isRunningOpponent) {
        updateTimerOpponent();
        animationFrame = requestAnimationFrame(loop); // Keeping the update
    }
}

// Start or continue a timer
function startTimer(doIncrement, playerType, time) {
    // Game is finished, do not launch timer again
    if (GAMESTATUS === 'finished') {
        return;
    }

    if (playerType === 'player') {
        if (!isRunningPlayer) {
            if (doIncrement) {
                remainingTimeOpponent += (parseInt(INCREMENT) * 1000);
            }
            if (typeof time !== 'undefined') {
                remainingTimePlayer = time * 1000;
            }
            endTimePlayer = Date.now() + remainingTimePlayer; // Update endtime
            isRunningPlayer = true;
            animationFrame = requestAnimationFrame(loop); // Start the loop
        }

        $('.timer-bottom').addClass('active');
    } else {
        if (!isRunningOpponent) {
            if (doIncrement) {
                remainingTimePlayer += (parseInt(INCREMENT) * 1000);
            }
            if (typeof time !== 'undefined') {
                remainingTimeOpponent = time * 1000;
            }
            endTimeOpponent = Date.now() + remainingTimeOpponent; // Update endtime
            isRunningOpponent = true;
            animationFrame = requestAnimationFrame(loop); // Start the loop
        }

        $('.timer-top').addClass('active');
    }
}


// Stop timer
function stopTimer(playerType) {
    if (playerType === 'player') {
        if (isRunningPlayer) {
            isRunningPlayer = false;
            cancelAnimationFrame(animationFrame); // Stop the update, this will stop the loop
        }

        $('.timer-bottom').removeClass('active');
    } else if (playerType === 'opponent') {
        if (isRunningOpponent) {
            isRunningOpponent = false;
            cancelAnimationFrame(animationFrame); // Stop the update, this will stop the loop
        }

        $('.timer-top').removeClass('active');
    }
}

// Switch turn, stop one timer and start the other one
function switchTurn() {
    if (isRunningPlayer) {
        stopTimer('player');
        startTimer(true, 'opponent');
    } else if (isRunningOpponent) {
        stopTimer('opponent');
        startTimer(true, 'player');
    }
}


function gameIsOver(playerWinner, reasonMessage, socketMessage) {
    // Game is already mark as finished, do not process this again
    if (GAMESTATUS === 'finished') {
        return;
    }

    GAMESTATUS = 'finished';

    stopTimer('player');
    stopTimer('opponent');

    if ('vibrate' in window.navigator) {
        try {
            window.navigator.vibrate(100);
        } catch (error) {
            console.log(error);
        }
    }

    if (typeof socketMessage !== 'undefined' && socketMessage === 'timeout') {
        socket.send(JSON.stringify({
            'method': socketMessage,
            'color': PLAYERCOLOR,
            'colorWinner': playerWinner,
            'idGame': IDGAME,
        }));
    }

    $('.tchat').append('<div>' + reasonMessage + '</div>');

    $('.piece.' + PLAYERCOLOR).draggable('destroy');

    // The player is the winner
    if (playerWinner === PLAYERCOLOR) {
        lauchFireworks('#fireworks-container');
    }
}

function jsonToQueryString(json) {
    return Object.entries(json)
        .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(value)}`)
        .join('&');
}
