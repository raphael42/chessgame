import $ from 'jquery';
import 'jquery-ui/ui/widgets/draggable';
import 'jquery-ui/ui/widgets/droppable';
import { Chess } from 'chess.js';
require('bootstrap');

$(function() {
    const socket = new WebSocket('ws://localhost:3001');
    const chess = new Chess(FEN);

    socket.addEventListener('open', function(e) {
        console.log('open', e);
    });

    socket.addEventListener('message', function(e) {
        var socketMessage = JSON.parse(e.data);

        if (typeof socketMessage.opponent_disconnect !== 'undefined' && socketMessage.opponent_disconnect === true) {
            $('.opponent-connect').html('KO');
            return;
        }

        if (typeof socketMessage.opponent_connect !== 'undefined' && socketMessage.opponent_connect === true) {
            $('.opponent-connect').html('OK');

            let fenSplit = chess.fen().split(' ');
            let fenTurnNumber = parseInt(fenSplit[5]);
            if (fenTurnNumber >= 2 && typeof times === 'undefined') { // Timer must have started and is not started
                if (fenSplit[1] === 'b') { // Black turn
                    if (PLAYERCOLOR === 'black') {
                        // Set up player timer
                        startTimer('player', socketMessage.blackMicrotimeSpend);
                    } else {
                        // Set up opponent timer
                        startTimer('opponent', null, socketMessage.blackMicrotimeSpend);
                    }
                } else { // White turn
                    if (PLAYERCOLOR === 'white') {
                        // Set up player timer
                        startTimer('player', socketMessage.whiteMicrotimeSpend);
                    } else {
                        // Set up opponent timer
                        startTimer('opponent', null, socketMessage.whiteMicrotimeSpend);
                    }
                }
            }
            return;
        }

        if (socketMessage.flag === 'k') { // king side castelling
            if (socketMessage.color === 'white') {
                var img = $('#h1').html();
                $('#h1').empty();
                $('#f1').html(img);
                $('#f1 img').draggable({
                    revert: true,
                });
            } else if (socketMessage.color === 'black') {
                var img = $('#h8').html();
                $('#h8').empty();
                $('#f8').html(img);
                $('#f8 img').draggable({
                    revert: true,
                });
            }
        } else if (socketMessage.flag === 'q') { // queen side castelling
            if (socketMessage.color === 'white') {
                var img = $('#a1').html();
                $('#a1').empty();
                $('#d1').html(img);
                $('#d1 img').draggable({
                    revert: true,
                });
            } else if (socketMessage.color === 'black') {
                var img = $('#a8').html();
                $('#a8').empty();
                $('#d8').html(img);
                $('#d8 img').draggable({
                    revert: true,
                });
            }
        } else if (socketMessage.flag === 'e') { // en passant capture
            if (socketMessage.color === 'white') {
                var tmp = (socketMessage.to).split('');
                var idPawnCatured = tmp[0] + (parseInt(tmp[1]) - 1);
            } else if (socketMessage.color === 'black') {
                var tmp = (socketMessage.to).split('');
                var idPawnCatured = tmp[0] + (parseInt(tmp[1]) + 1);
            }
            $('#' + idPawnCatured).empty();
        }

        if (typeof socketMessage.promotion !== 'undefined' && socketMessage.promotion !== null) { // promotion
            var piecesPromotion = {
                'r': 'rook',
                'n': 'knight',
                'b': 'bishop',
                'q': 'queen',
            };

            let src = PIECESIMGURL;
            src = src.replace('chessboard', piecesPromotion[socketMessage.promotion]);
            src = src.replace('playerColor', socketMessage.color);
            $('#' + socketMessage.from).empty();
            $('#' + socketMessage.to).html('<img class="piece ' + socketMessage.color + '" src="' + src + '" alt>');

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

            if (chess.isDraw() === true) {
                alert('Match nul !');
            }

            if (chess.isCheckmate() === true) {
                if (socketMessage.color === 'w') {
                    setWinner('blancs');
                } else if (socketMessage.color === 'b') {
                    setWinner('noirs');
                }
            }

            if (chess.inCheck() === true) {
                if (socketMessage.color === 'white') {
                    var kingposition = getKingPosition(socketMessage.fen, 'black');
                    $('#' + kingposition).addClass('in-check');
                } else if (socketMessage.color === 'black') {
                    var kingposition = getKingPosition(socketMessage.fen, 'white');
                    $('#' + kingposition).addClass('in-check');
                }
            }
            switchTurn();
        } else {
            var img = $('#' + socketMessage.from).html();
            $('#' + socketMessage.from).empty();
            $('#' + socketMessage.to).html(img);

            $('.in-check').removeClass('in-check');

            try {
                chess.move({
                    from: socketMessage.from,
                    to: socketMessage.to
                });
            } catch (error) {
                console.log(error);
            }

            if (chess.isDraw() === true) {
                alert('Match nul !');
            }

            if (chess.isCheckmate() === true) {
                if (socketMessage.color === 'white') {
                    setWinner('blancs');
                } else if (socketMessage.color === 'black') {
                    setWinner('noirs');
                }
            }

            if (chess.inCheck() === true) {
                if (socketMessage.color === 'white') {
                    var kingposition = getKingPosition(socketMessage.fen, 'black');
                    $('#' + kingposition).addClass('in-check');
                } else if (socketMessage.color === 'black') {
                    var kingposition = getKingPosition(socketMessage.fen, 'white');
                    $('#' + kingposition).addClass('in-check');
                }
            }

            if (typeof socketMessage.fen !== 'undefined') {
                var tmp = socketMessage.fen.split(' ');
                var tmp2 = parseInt(tmp[5]);
                if (tmp2 === 2 && socketMessage.color === 'black') {
                    startTimer('player');
                } else {
                    updateTime('opponent', socketMessage.timer); // update time because of lantency
                    switchTurn();
                }
            }
        }

        if (socketMessage.color === 'w') { // White play, make a new line
            let htmlMoveRow = '' +
            '<div class="row move-' + socketMessage.moveNumber + '">' +
                '<div class="col-4">' + socketMessage.moveNumber + '</div>' +
                '<div class="col-4 move-san-white-' + socketMessage.moveNumber + '">' + socketMessage.san + '</div>' +
                '<div class="col-4 move-san-black-' + socketMessage.moveNumber + '"></div>' +
            '</div>';

            $('.history').append(htmlMoveRow);
        } else { // Black play, complete the line
            $('.history').find('.move-san-black-' + socketMessage.moveNumber).html(socketMessage.san);
        }

        $('.chess-table.last-move').each(function() {
            $(this).removeClass('last-move');
        });
        $('#' + socketMessage.from).addClass('last-move');
        $('#' + socketMessage.to).addClass('last-move');

        if (typeof socketMessage.idGame === 'undefined') {
            $('#player-turn').text('À votre tour !');
        }

        // 2 squares premove, execute the move isntantly
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
                if (squareFromInfos !== null && squareFromInfos['type'] === 'p' && ((PLAYERCOLOR === 'white' && squareFromInfos['color'] === 'w' && idFromLine === 7 && idToLine === 8) || (PLAYERCOLOR === 'black' && squareFromInfos['color'] === 'b' && idFromLine === 2 && idToLine === 1))) {
                    promotionPiece(function(promotion) {
                        processMove(chess, socket, squareIdFrom, squareIdTo, promotion);
                    });
                } else {
                    processMove(chess, socket, squareIdFrom, squareIdTo, promotion);
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

    $('#board').off().on('click', 'td', function() {
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
                (PLAYERCOLOR === 'white' && playerTurn === 'b') ||
                (PLAYERCOLOR === 'black' && playerTurn === 'w')
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
            if (squareFromInfos !== null && squareFromInfos['type'] === 'p' && ((PLAYERCOLOR === 'white' && squareFromInfos['color'] === 'w' && idFromLine === 7 && idToLine === 8) || (PLAYERCOLOR === 'black' && squareFromInfos['color'] === 'b' && idFromLine === 2 && idToLine === 1))) {
                promotionPiece(function(promotion) {
                    processMove(chess, socket, squareIdFrom, squareIdTo, promotion);
                });
            } else {
                processMove(chess, socket, squareIdFrom, squareIdTo, promotion);
            }
        }

        // Second click to premove in no piece square
        if ($('#board').find('.clicked-premove').length === 1 && !$(self).hasClass('clicked-premove')) {
            $(self).addClass('clicked-premove');
            $(self).data('premove', 2);
        }
    });

    $('.piece.' + PLAYERCOLOR).draggable({
        revert: true,
        start: function(ev, ui) {
            $('.chess-table.clicked-premove').removeClass('clicked-premove');
            const self = $(this);
            let playerTurn = chess.turn(); // Which player turn it is
            if (
                (PLAYERCOLOR === 'white' && playerTurn === 'b') ||
                (PLAYERCOLOR === 'black' && playerTurn === 'w')
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
            $('.chess-table.clicked').removeClass('clicked');
            $('.chess-table.possible-move').each(function() {
                $(this).removeClass('possible-move');
            });
        }
    });

    $('.chess-table').droppable({
        drop: function(ev, ui) {
            let playerTurn = chess.turn(); // Which player turn it is
            if (
                (PLAYERCOLOR === 'white' && playerTurn === 'b') ||
                (PLAYERCOLOR === 'black' && playerTurn === 'w')
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
                if (squareFromInfos !== null && squareFromInfos['type'] === 'p' && ((PLAYERCOLOR === 'white' && squareFromInfos['color'] === 'w' && idFromLine === 7 && idToLine === 8) || (PLAYERCOLOR === 'black' && squareFromInfos['color'] === 'b' && idFromLine === 2 && idToLine === 1))) {
                    promotionPiece(function(promotion) {
                        processMove(chess, socket, squareIdFrom, squareIdTo, promotion);
                    });
                } else {
                    processMove(chess, socket, squareIdFrom, squareIdTo, promotion);
                }
            }
        }
    });
});

function processMove(chess, socket, squareIdFrom, squareIdTo, promotion) {
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
        console.log(error);
    }

    if (moving !== null) {
        if (chess.inCheck() === true) {
            if (moving.color === 'w') {
                var kingposition = getKingPosition(chess.fen(), 'black');
                $('#' + kingposition).addClass('in-check');
            } else if (moving.color === 'b') {
                var kingposition = getKingPosition(chess.fen(), 'white');
                $('#' + kingposition).addClass('in-check');
            }
        }

        if (wasInCheck) {
            $('.in-check').removeClass('in-check');
        }
        if (moving.flags === 'k') { // king side castelling
            if (PLAYERCOLOR === 'white') {
                var img = $('#h1').html();
                $('#h1').empty();
                $('#f1').html(img);
                $('#f1 img').draggable({
                    revert: true,
                });
            } else if (PLAYERCOLOR === 'black') {
                var img = $('#h8').html();
                $('#h8').empty();
                $('#f8').html(img);
                $('#f8 img').draggable({
                    revert: true,
                });
            }
        } else if (moving.flags === 'q') { // queen side castelling
            if (PLAYERCOLOR === 'white') {
                var img = $('#a1').html();
                $('#a1').empty();
                $('#d1').html(img);
                $('#d1 img').draggable({
                    revert: true,
                });
            } else if (PLAYERCOLOR === 'black') {
                var img = $('#a8').html();
                $('#a8').empty();
                $('#d8').html(img);
                $('#d8 img').draggable({
                    revert: true,
                });
            }
        } else if (moving.flags === 'e') { // en passant capture
            if (PLAYERCOLOR === 'white') {
                var tmp = (moving.to).split('');
                var idPawnCatured = tmp[0] + (parseInt(tmp[1]) - 1);
            } else if (PLAYERCOLOR === 'black') {
                var tmp = (moving.to).split('');
                var idPawnCatured = tmp[0] + (parseInt(tmp[1]) + 1);
            }
            $('#' + idPawnCatured).empty();
        }

        if ($('#' + squareIdTo).html() !== '') {
            $('#' + squareIdTo).empty();
        }

        $('#' + squareIdFrom + ' img').detach().css({top: 0, left: 0}).appendTo('#' + squareIdTo);

        if (promotion !== null) {
            let src = PIECESIMGURL;
            src = src.replace('chessboard', piecesPromotion[promotion]);
            src = src.replace('playerColor', PLAYERCOLOR);
            $('#' + squareIdTo).html('<img class="piece ' + PLAYERCOLOR + '" src="' + src + '" alt>');
            $('#' + squareIdTo + ' img').draggable({
                revert: true,
            });
        }

        let historyVerbose = chess.history({verbose: true});
        let lastMoveHistory = historyVerbose[historyVerbose.length - 1];
        lastMoveHistory['idGame'] = IDGAME;
        lastMoveHistory['moveNumber'] = moveNumber;

        if (moving.color === 'w') { // White play, make a new line
            let htmlMoveRow = '' +
            '<div class="row move-' + lastMoveHistory.moveNumber + '">' +
                '<div class="col-4">' + lastMoveHistory.moveNumber + '</div>' +
                '<div class="col-4 move-san-white-' + lastMoveHistory.moveNumber + '">' + lastMoveHistory.san + '</div>' +
                '<div class="col-4 move-san-black-' + lastMoveHistory.moveNumber + '"></div>' +
            '</div>';

            $('.history').append(htmlMoveRow);
        } else { // Black play, complete the line
            $('.history').find('.move-san-black-' + lastMoveHistory.moveNumber).html(lastMoveHistory.san);
        }

        try {
            socket.send(JSON.stringify(lastMoveHistory));
        } catch (error) {
            console.log('Socket error', error);
        }

        var tmp = chess.fen().split(' ');
        var tmp2 = parseInt(tmp[5]);
        if (tmp2 === 2 && moving.color === 'b') {
            startTimer('opponent');
        } else {
            switchTurn();
        }

        $('#player-turn').text('En attente de l\'adversaire');

        if (chess.isCheckmate() === true) {
            if (moving.color === 'w') {
                setWinner('blancs');
            } else if (moving.color === 'b') {
                setWinner('noirs');
            }
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

        return true;
    }

    $('.chess-table.clicked-premove').each(function() {
        $(this).removeClass('clicked-premove');
    });

    return false;
}

function promotionPiece(callback) {
    $('#promotion-modal').modal('show');
    $('#promotion-modal button').on('click', function() {
        var piece = $(this).attr('id');
        $('#promotion-modal').modal('hide');
        callback(piece);
    });
}

function getKingPosition(fen, color) {
    var tmp = fen.split(' ');
    var tmp2 = tmp[0].split('/');

    var piecesLabel = {
        'r': 'black-rook',
        'n': 'black-knight',
        'b': 'black-bishop',
        'q': 'black-queen',
        'k': 'black-king',
        'p': 'black-pawn',
        'R': 'white-rook',
        'N': 'white-knight',
        'B': 'white-bishop',
        'Q': 'white-queen',
        'K': 'white-king',
        'P': 'white-pawn',
    };

    var column = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
    var columnKey = 0;
    var line = 8;
    var chessboard = [];
    var atom = '';

    for (var i in tmp2) {
        atom = tmp2[i].split('');
        columnKey = 0;
        for (var j in atom) {
            if ($.inArray(atom[j], ['r', 'n', 'b', 'q', 'k', 'p', 'R', 'N', 'B', 'Q', 'K', 'P']) !== -1) {
                if ((color === 'white' && atom[j] === 'K') || (color === 'black' && atom[j] === 'k')) {
                    return column[columnKey] + line;
                }
                columnKey += 1;
            } else {
                columnKey += parseInt(atom[j]);
            }
        }
        line--;
    }

    return null;
}

function placePieces() {
    var tmp = FEN.split(' ');
    var tmp2 = tmp[0].split('/');

    if (PLAYERCOLOR === 'white') {
        if (tmp[1] === 'w') {
            $('#player-turn').text('À votre tour !');
        } else {
            $('#player-turn').text('En attente de l\'adversaire');
        }
    } else {
        if (tmp[1] === 'w') {
            $('#player-turn').text('En attente de l\'adversaire');
        } else {
            $('#player-turn').text('À votre tour !');
        }
    }

    var piecesLabel = {
        'r': 'black-rook',
        'n': 'black-knight',
        'b': 'black-bishop',
        'q': 'black-queen',
        'k': 'black-king',
        'p': 'black-pawn',
        'R': 'white-rook',
        'N': 'white-knight',
        'B': 'white-bishop',
        'Q': 'white-queen',
        'K': 'white-king',
        'P': 'white-pawn',
    };

    var column = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
    var columnKey = 0;
    var line = 8;
    var chessboard = [];
    var atom = '';
    for (var i in tmp2) {
        atom = tmp2[i].split('');
        columnKey = 0;
        for (var j in atom) {
            if ($.inArray(atom[j], ['r', 'n', 'b', 'q', 'k', 'p', 'R', 'N', 'B', 'Q', 'K', 'P']) !== -1) {
                chessboard[column[columnKey] + line] = piecesLabel[atom[j]];
                columnKey += 1;
            } else {
                columnKey += parseInt(atom[j]);
            }
        }
        line--;
    }

    let src = null;
    var color = null;
    for (var k in chessboard) {
        src = PIECESIMGURL;
        src = src.replace("playerColor-chessboard", chessboard[k]);

        color = 'white';
        if (chessboard[k].indexOf('white') == -1){
            color = 'black';
        }
        $('#' + k).html('<img class="piece ' + color + '" src="' + src + '" alt>');
    }

    let lastMoveHistory = MOVES[MOVES.length - 1];
    if (typeof lastMoveHistory !== 'undefined') {
        $('#' + lastMoveHistory.square_from).addClass('last-move');
        $('#' + lastMoveHistory.square_to).addClass('last-move');
    }
}

// timer functions
function getTime(time) {
    var min = Math.floor(time / 60);
    var sec = time % 60;
    return min + ':' + (sec < 10 ? '0' + sec : sec);
}

function getSeconds(min, sec) {
    var time = min * 60 + sec;
    return time;
}

function getSecondsWithTime(time, splitChar) {
    var splitTime = time.split(splitChar);
    var seconds = splitTime[0] * 60;
    if (typeof splitTime[1] !== 'undefined') {
        seconds += parseInt(splitTime[1]);
    }
    return seconds;
}

function switchTurn() {
    if (typeof times !== 'undefined') { // undefined the first moves, when the timer is not started yet
        times[turn] += parseInt(INCREMENT);
    }
    if (turn === 'player') {
        turn = 'opponent';
    } else {
        turn = 'player';
    }
}

function updateTime(playerTurn, time) {
    $('#timer-' + playerTurn).text(getTime(time));

    return;
}

function setUpTimer(playerTime, opponentTime) {
    let playerTimeToUse = PLAYERTIMELEFT;
    if (typeof playerTime !== 'undefined' && playerTime !== null) {
        playerTimeToUse = playerTime;
    }

    let opponentTimeToUse = OPPONENTTIMELEFT;
    if (typeof opponentTime !== 'undefined' && opponentTime !== null) {
        opponentTimeToUse = opponentTime;
    }

    var minutesPlayer = parseInt(playerTimeToUse / 60, 10);
    var secondsPlayer = parseInt(playerTimeToUse % 60, 10);

    var minutesOpponent = parseInt(opponentTimeToUse / 60, 10);
    var secondsOpponent = parseInt(opponentTimeToUse % 60, 10);

    $('#timer-player').text(getTime(getSeconds(minutesPlayer, secondsPlayer)));
    $('#timer-opponent').text(getTime(getSeconds(minutesOpponent, secondsOpponent)));

    return {
        player: getSeconds(minutesPlayer, secondsPlayer),
        opponent: getSeconds(minutesOpponent, secondsOpponent),
    };
}

function startTimer(playerTurn, playerTime, opponentTime) {
    times = setUpTimer(playerTime, opponentTime);

    turn = playerTurn;

    timer = setInterval(function() {
        times[turn]--;

        $('#timer-player').text(getTime(times['player']));
        $('#timer-opponent').text(getTime(times['opponent']));

        if (times[turn] == 0) {
            navigator.vibrate(1000);
            clearInterval(timer);
            timer = false;

            if ((turn === 'player' && playerColor === 'white') || (turn === 'opponent' && playerColor === 'black')) {
                setWinner('noirs');
            } else if ((turn === 'player' && playerColor === 'black') || (turn === 'opponent' && playerColor === 'white')) {
                setWinner('blancs');
            }
        }
    }, 1000);
}

function stopTimer() {
    clearInterval(timer);
    timer = false;
}

function setWinner(playerColor) {
    alert('Victoire des '+ playerColor +' !');
    stopTimer()
    $('#player-turn').text('Victoire des '+ playerColor +' !');
}

var turn = null;
var times, timer;
placePieces();
setUpTimer();