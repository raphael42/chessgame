import $ from 'jquery';
import 'jquery-ui/ui/widgets/draggable';
import 'jquery-ui/ui/widgets/droppable';
import { Chess } from 'chess.js';
require('bootstrap');

placePieces(FEN);

let fenChessJs = FEN.replaceAll('*', 'p');
const chess = new Chess();

chess.load(fenChessJs, {
    skipValidation: true
});

console.log(NEXTCHALLENGEPATH);
// document.location.href = 'apprendre/' + SLUG + '/' + ID;

let nbMoves = 0;

$(function() {
    $('#board').off().on('click', '.chess-table', function() {
        const self = $(this);
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

            var allPossibleMoves = chess.moves({
                square: idSquare,
                verbose: true
            });

            for (var i in allPossibleMoves) {
                if (allPossibleMoves[i].from === idSquare) {
                    $('#' + allPossibleMoves[i].to).addClass('possible-move');
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
            const self = $(this);
            $(self).parent().addClass('clicked');

            var allPossibleMoves = chess.moves({
                square: $(self).parent().attr('id'),
                verbose: true
            });

            for (var i in allPossibleMoves) {
                if (allPossibleMoves[i].from === $(self).parent().attr('id')) {
                    $('#' + allPossibleMoves[i].to).addClass('possible-move');
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
        }
    }

    if (moving !== null) {
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

        // Set the white turn back
        let newFen = chess.fen();
        newFen = newFen.replace(' b ', ' w ');
        chess.load(newFen, {
            skipValidation: true
        });

        const regexStar = /\p/;

        console.log(newFen);
        console.log(regexStar.test(newFen));

        // TODO : no stars in the FEN, must find a solution here

        if (!regexStar.test(newFen)) { // There is not stars anymore, save the challenge and redirect to the new one
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
                    console.log(data);
                }
            });

            // If there is a next challenge, redirect to it
            if (NEXTCHALLENGEPATH !== null) {
                document.location.href = NEXTCHALLENGEPATH;
            } else { // If it's the last, display the final modal
                // TODO : display a modal

                $('#final-modal').modal('show');
            }
        }

        return true;
    }

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
