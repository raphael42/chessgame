import $, { inArray } from 'jquery';
import 'jquery-ui/ui/widgets/draggable';
import 'jquery-ui/ui/widgets/droppable';
import { Chess } from 'chess.js';
require('bootstrap');

let starsPositions = [];
placePieces(FEN);

// If the game is lost, this variable will change to true
let gameLost = false;

// If the challenge is done, this variable will change to true
let challengeDone = false;

let starsPieces = false;
if (FEN.includes('*')) { // If a star is in FEN, do not activate the black captures
    starsPieces = true;
}

const chess = new Chess();

// let fenChessJs = FEN.replaceAll('*', 'p');
let fenChessJs = FEN;
chess.load(fenChessJs, {
    skipValidation: true
});

let nbMoves = 0;

$(function() {
    if (chess.inCheck() === true) {
        let kingposition = null;
        if (chess.turn() === 'w') {
            kingposition = getKingPosition(FEN, 'white');
        } else {
            kingposition = getKingPosition(FEN, 'black');
        }

        $('#' + kingposition).addClass('in-check');
    }

    $('#board').off().on('click', '.chess-table', function() {
        if (gameLost === true || challengeDone === true) {
            return;
        }

        var idSquare = $(this).attr('id');

        // If all squares has the class clicked-premove-hover because a premove was made before, remove it
        $('.chess-table').each(function() {
            $(this).removeClass('clicked-premove-hover');
        });

        // Select one of the player piece
        if ($(this).find('.piece.white').length === 1) {
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
            setPossibleMovesClass(idSquare, true);
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

            if (squareFromInfos !== null && squareFromInfos['type'] === 'p' && squareFromInfos['color'] === 'w' && idFromLine === 7 && idToLine === 8) {
                promotionPiece(function(promotion) {
                    processMove(squareIdFrom, squareIdTo, promotion);
                });
            } else {
                processMove(squareIdFrom, squareIdTo, null);
            }
        }
    });

    setupDraggable();

    $('.chess-table').droppable({
        drop: function(ev, ui) {
            if (gameLost === true || challengeDone === true) {
                return;
            }

            // Set back z-index to the element dropped
            $(ui.helper[0]).css('z-index', 2);

            var squareIdFrom = (ui.draggable).parent().attr('id');
            var squareIdTo = $(this).attr('id');

            var squareFromInfos = chess.get(squareIdFrom);

            var tmp2 = squareIdFrom.split('');
            var idFromLine = parseInt(tmp2[1]);

            var tmp3 = squareIdTo.split('');
            var idToLine = parseInt(tmp3[1]);

            var promotion = null;
            if (squareFromInfos !== null && squareFromInfos['type'] === 'p' && squareFromInfos['color'] === 'w' && idFromLine === 7 && idToLine === 8) {
                promotionPiece(function(promotion) {
                    processMove(squareIdFrom, squareIdTo, promotion);
                });
            } else {
                processMove(squareIdFrom, squareIdTo, promotion);
            }
        }
    });
});

function setupDraggable(jQueryElement) {
    let elementToDraggable = '.piece.white';
    if (typeof jQueryElement !== 'undefined') {
        elementToDraggable = jQueryElement;
    }

    $(elementToDraggable).draggable({
        revert: true,
        start: function(ev, ui) {
            if (gameLost === true || challengeDone === true) {
                return;
            }

            $(this).parent().addClass('clicked');
            let idSquare = $(this).parent().attr('id');

            setPossibleMovesClass(idSquare, true);
        },
        stop: function(ev, ui) {
            const targetElement = $(document.elementFromPoint(
                ev.clientX,
                ev.clientY
            ));

            // If target element if is undefined, it's the same square
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

function placePieces(fen) {
    // First remove all pieces if there is some
    $('.chess-table').each(function() {
        let pieceImg = $(this).find('img');
        if (pieceImg.length > 0) {
            pieceImg.remove();
        }
    });

    var fenSplit = fen.split(' ');
    var fenSplitPieces = fenSplit[0].split('/');

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
        '*': 'star',
    };

    var column = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
    var columnKey = 0;
    var line = 8;
    var chessboard = [];
    var count = '';
    for (var i in fenSplitPieces) {
        count = fenSplitPieces[i].split('');
        columnKey = 0;
        for (var j in count) {
            if ($.inArray(count[j], ['r', 'n', 'b', 'q', 'k', 'p', 'R', 'N', 'B', 'Q', 'K', 'P', '*']) !== -1) {
                chessboard[column[columnKey] + line] = piecesLabel[count[j]];
                if (count[j] === '*') {
                    starsPositions.push(column[columnKey] + line);
                }
                columnKey += 1;
            } else {
                columnKey += parseInt(count[j]);
            }
        }
        line--;
    }

    let src = null;
    var color = null;
    for (var k in chessboard) {
        if (chessboard[k] === 'star') {
            src = PIECESIMGURL;
            src = src.replace('playerColor-chessboard', chessboard[k]);
            src = src.replace('png', 'svg');
            $('#' + k).append('<img class="piece black" src="' + src + '" alt>');
        } else {
            src = PIECESIMGURL;
            src = src.replace('playerColor-chessboard', chessboard[k]);

            color = 'white';
            if (chessboard[k].indexOf('white') == -1){
                color = 'black';
            }
            $('#' + k).append('<img class="piece ' + color + '" src="' + src + '" alt>');
        }
    }
}

function processMove(squareIdFrom, squareIdTo, promotion) {
    var piecesPromotion = {
        'r': 'rook',
        'n': 'knight',
        'b': 'bishop',
        'q': 'queen',
    };

    // If the move is the same square, do nothing
    if (squareIdFrom === squareIdTo) {
        return;
    }

    var moving = null;

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
        } else {
            if (SLUG === 'outcheck' && $('#' + squareIdTo).hasClass('possible-move')) { // outcheck challenge, if the move is invalid, game is lost
                gameLost = true;
                moving = {
                    'color': 'w',
                    'flags': 'illegal-incheck',
                };
            }
        }
    }

    if (moving !== null) {
        let inCheck = chess.inCheck();
        $('.in-check').removeClass('in-check');
        if (moving.flags === 'illegal-incheck') {
            let kingposition = getKingPosition(chess.fen(), 'white');
            if (kingposition === squareIdFrom) { // king moved, keep it in check in his new square
                $('#' + squareIdTo).addClass('in-check');
            } else { // king didn't move, keep it in check in his original square
                $('#' + kingposition).addClass('in-check');
            }

            $('#challenge-description').html('Vous avez perdu !<br><button class="mt-1 btn btn-danger" onclick="window.location.reload()">Réessayez</button>');
            $('#challenge-description').addClass('alert alert-danger');
        } else {
            if (inCheck) {
                let kingposition = getKingPosition(chess.fen(), 'black');
                $('#' + kingposition).addClass('in-check');
            }
        }

        if ($('#' + squareIdTo).find('img').length > 0) {
            $('#' + squareIdTo + ' img').remove();
        }

        if (promotion !== null) {
            if ($('#' + squareIdFrom).find('img').length > 0) {
                $('#' + squareIdFrom + ' img').remove();
            }

            let color = 'white';
            if (moving.color === 'b') {
                color = 'black';
            }
            let src = PIECESIMGURL;
            src = src.replace('chessboard', piecesPromotion[promotion]);
            src = src.replace('playerColor', color);
            $('#' + squareIdTo).append('<img class="piece ' + color + '" src="' + src + '" alt>');
            setupDraggable('#' + squareIdTo + ' img');
        } else {
            // css top and left are set because when dropping piece, a strange thing happen
            $('#' + squareIdFrom + ' img').detach().css({top: 0, left: 0}).appendTo('#' + squareIdTo);
        }

        $('.chess-table.clicked').removeClass('clicked');
        $('.chess-table.possible-move').each(function() {
            $(this).removeClass('possible-move');
        });

        // Increament moves number
        nbMoves++;

        // Check blacks moves to see if they can capture our pieces
        if (!starsPieces && SLUG !== 'outcheck') { // If there is starsPieces, the capture is not activated
            // We get all pieces in the board
            const chessBoard = chess.board();

            // For each lines found
            for (let oneLine in chessBoard) {
                for (let oneSquare in chessBoard[oneLine]) {
                    if (chessBoard[oneLine][oneSquare] !== null && chessBoard[oneLine][oneSquare].color === 'b') {
                        let allPossibleMoves = chess.moves({
                            square: chessBoard[oneLine][oneSquare].square,
                            verbose: true
                        });

                        for (let oneMove in allPossibleMoves) {
                            if (allPossibleMoves[oneMove].flags === 'c') {
                                const chess2 = new Chess();
                                chess2.load(allPossibleMoves[oneMove].before, {
                                    skipValidation: true
                                });

                                // Make the white move
                                chess2.move({
                                    from: allPossibleMoves[oneMove].from,
                                    to: allPossibleMoves[oneMove].to,
                                    promotion: null,
                                });

                                // Black piece is attacked after his move, our original piece was proctected
                                const isAttacked = chess2.isAttacked(allPossibleMoves[oneMove].to, 'w');
                                if (isAttacked === true) {
                                    break;
                                }

                                // If a piece can be captured, the game is lost
                                gameLost = true;

                                $('#challenge-description').html('Vous avez perdu !<br><button class="mt-1 btn btn-danger" onclick="window.location.reload()">Réessayez</button>');
                                $('#challenge-description').addClass('alert alert-danger');

                                $('#' + allPossibleMoves[oneMove].from).append('<i class="bi bi-exclamation-circle d-inline-flex text-bg-danger rounded-circle exclamation-capture"></i>');

                                try {
                                    chess.move({
                                        from: allPossibleMoves[oneMove].from,
                                        to: allPossibleMoves[oneMove].to,
                                        promotion: null,
                                    });
                                } catch (error) {
                                    let regex = /Invalid move/;
                                    if (!regex.test(error)) { // Do not display a log if it's an invalid move
                                        console.log(error);
                                    }
                                }

                                setTimeout(() => {
                                    if ($('#' + allPossibleMoves[oneMove].to).find('img').length > 0) {
                                        $('#' + allPossibleMoves[oneMove].to + ' img').remove();
                                    }
                                    $('#' + allPossibleMoves[oneMove].from + ' img').detach().css({top: 0, left: 0}).appendTo('#' + allPossibleMoves[oneMove].to);

                                    $('.exclamation-capture').remove();

                                    $('#' + allPossibleMoves[oneMove].from).addClass('last-move');
                                    $('#' + allPossibleMoves[oneMove].to).addClass('last-move');

                                    $('.in-check').removeClass('in-check');
                                }, '500');

                                break;
                            }
                        }
                    }
                }
            }
        } else { // If there is starsPieces, we check if the move done captured a star
            if (inArray(squareIdTo, starsPositions) !== -1) {
                starsPositions.splice(inArray(squareIdTo, starsPositions), 1); // Remove the star from the array
            }
        }

        if (SLUG === 'checkmate') {
            if (chess.isCheckmate() === true) {
                challengeDone = true;
            } else {
                gameLost = true;
                $('#challenge-description').html('Vous avez perdu !<br><button class="mt-1 btn btn-danger" onclick="window.location.reload()">Réessayez</button>');
                $('#challenge-description').addClass('alert alert-danger');

                try {
                    let moves = chess.moves({
                        verbose: true
                    });

                    if (moves.length > 0 && chess.inCheck() === true) {
                        let from = moves[0].from;
                        let to = moves[0].to;

                        setTimeout(() => {
                            if ($('#' + to).find('img').length > 0) {
                                $('#' + to + ' img').remove();
                            }
                            $('#' + from + ' img').detach().css({top: 0, left: 0}).appendTo('#' + to);

                            $('.in-check').removeClass('in-check');
                        }, '400');
                    }
                } catch (error) {
                    console.log(error);
                }
            }
        }

        if (!gameLost) {
            if (starsPieces) { // It's a game with stars, we need to check if the stars are still in the board. If not, the game is win
                if (starsPositions.length < 1) {
                    challengeDone = true;
                }
            } else if (inArray(SLUG, ['protect', 'outcheck']) !== -1) { // If the game mode is 'protect' or 'outcheck', it is win when blacks can't attack our pieces
                if (!gameLost) {
                    challengeDone = true;
                }
            } else if (SLUG === 'check') { // If the game mode is 'check', it is win when black are in check
                if (inCheck) {
                    challengeDone = true;
                } else {
                    gameLost = true;
                }
            } else { // Challenge ends when there is no black pieces anymore
                let fen = chess.fen();
                let fenSplit = fen.split(' ');
                let fenToTest = fenSplit[0];

                const regexStar = /[rnbqkp]/;

                if (!regexStar.test(fenToTest)) { // Check if there is black piece in the board
                    challengeDone = true;
                }
            }
        }

        // Set the white turn back
        let fenWhiteTurn = chess.fen();
        fenWhiteTurn = fenWhiteTurn.replace(' b ', ' w ');
        chess.load(fenWhiteTurn, {
            skipValidation: true
        });

        // Challenge is finished
        if (challengeDone) {
            let scoreDifference = SCOREGOAL - nbMoves;

            let scoreObtained = 1; // 1 point default
            if (scoreDifference === 0) { // Score same as the goal, 3 points
                scoreObtained = 3;
            } else if (scoreDifference === -1 || scoreDifference === -2) { // 2 moves more than the goal, 2 points
                scoreObtained = 2;
            }
            let htmlStars = '';
            while (scoreObtained !== 0) {
                htmlStars += '<i class="bi bi-star-fill"></i>';
                scoreObtained--;
            }

            $('#challenge-description').html('Félicitation !<br><span class="mt-1">' + htmlStars + '</span>');
            $('#challenge-description').addClass('alert alert-success');

            const data = {
                'category': SLUG,
                'id': ID,
                'nb_moves': nbMoves,
            };

            $.ajax({
                url: AJAXSAVECHALLENGE,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                error: function(xhr, status, error) {
                    console.log(status + ' : ' + error);
                    var errorMessage = xhr.status + ': ' + xhr.statusText
                    console.log('Error - ' + errorMessage);
                },
                success: function(data) {
                    // If there is a next challenge, redirect to it
                    if (NEXTCHALLENGEPATH !== null) {
                        setTimeout(() => {
                            document.location.href = NEXTCHALLENGEPATH;
                        }, '200');
                    } else { // If it's the last, display the final modal
                        $('#final-modal').modal('show');
                    }
                }
            });
        }

        // Keep the same piece selected to make the UI more firendly
        if (!gameLost && !challengeDone) {
            $('#' + squareIdTo).addClass('clicked');
            setPossibleMovesClass(squareIdTo, true);
        }

        return true;
    }

    return false;
}

function getKingPosition(fen, color) {
    var tmp = fen.split(' ');
    var tmp2 = tmp[0].split('/');
    var column = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
    var columnKey = 0;
    var line = 8;
    var count = '';

    for (var i in tmp2) {
        count = tmp2[i].split('');
        columnKey = 0;
        for (var j in count) {
            if ($.inArray(count[j], ['r', 'n', 'b', 'q', 'k', 'p', 'R', 'N', 'B', 'Q', 'K', 'P']) !== -1) {
                if ((color === 'white' && count[j] === 'K') || (color === 'black' && count[j] === 'k')) {
                    return column[columnKey] + line;
                }
                columnKey += 1;
            } else {
                columnKey += parseInt(count[j]);
            }
        }
        line--;
    }

    return null;
}

function getKingMoves(idSquare) {
    let arrLetters = [];
    let arrLines = [];

    let letter = idSquare.charAt(0);
    let letterMinus = null;
    if (letter !== 'a') {
        letterMinus = String.fromCharCode(letter.charCodeAt(0) - 1);
    }
    let letterPlus = null;
    if (letter !== 'h') {
        letterPlus = String.fromCharCode(letter.charCodeAt(0) + 1);
    }

    arrLetters.push(letter);
    arrLetters.push(letterMinus);
    arrLetters.push(letterPlus);

    let line = parseInt(idSquare.charAt(1));
    let lineMinus = null;
    if (line !== 1) {
        lineMinus = line - 1;
    }
    let linePlus = null;
    if (line !== 8) {
        linePlus = line + 1;
    }

    arrLines.push(line);
    arrLines.push(lineMinus);
    arrLines.push(linePlus);

    let arrPossibleMoves = [];
    for (let i in arrLetters) {
        for (let j in arrLines) {
            if (arrLetters[i] !== null && arrLines[j] !== null) {
                let square = arrLetters[i] + arrLines[j];
                if (square !== idSquare) {
                    let pieceSquare = chess.get(square);
                    if (typeof pieceSquare === 'object' && pieceSquare.color === 'w') {
                        continue;
                    }

                    arrPossibleMoves.push(square);
                }
            }
        }
    }

    return arrPossibleMoves;
}

function setPossibleMovesClass(idSquare, allowIllegalMoves = false) {
    let pieceSquare = chess.get(idSquare);
    let allPossibleMoves = [];
    // If king, make the moves ourself to display the illegal moves
    if (typeof pieceSquare !== 'undefined' && pieceSquare.type === 'k') {
        allPossibleMoves = getKingMoves(idSquare);
    } else {
        if (allowIllegalMoves) { // If we allow illegal moves, we replace our king by a pawn to get all moves
            let fen = chess.fen();
            fen = fen.replace('K', 'P');
            let chess2 = new Chess();
            chess2.load(fen, {
                skipValidation: true
            });

            let chessMoves = chess2.moves({
                square: idSquare,
                verbose: true
            });

            for (var i in chessMoves) {
                allPossibleMoves.push(chessMoves[i].to);
            }
        }

        let chessMoves = chess.moves({
            square: idSquare,
            verbose: true
        });

        for (var i in chessMoves) {
            allPossibleMoves.push(chessMoves[i].to);
        }
    }

    for (var i in allPossibleMoves) {
        $('#' + allPossibleMoves[i]).addClass('possible-move');
    }

    return true;
}

function promotionPiece(callback) {
    $('#promotion-modal').modal('show');
    $('#promotion-modal .promotion-piece-button').on('click', function() {
        var piece = $(this).attr('id');
        $('#promotion-modal').modal('hide');
        callback(piece);
    });
}
