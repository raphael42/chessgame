import { Fireworks } from 'fireworks-js';

/**
 * Launches a fireworks animation on the specified HTML element.
 *
 * @param {string} element - A CSS selector string representing the HTML element
 *                           where the fireworks animation will be displayed.
 *
 * The function initializes a fireworks animation with customizable options such as
 * speed, gravity, particle count, and more. The animation starts immediately and
 * gradually reduces its intensity after 2 seconds. It stops completely after an
 * additional 3 seconds.
 *
 * Dependencies:
 * - The `Fireworks` class must be available in the global scope for this function to work.
 *
 * Example usage:
 * ```javascript
 * lauchFireworks('#fireworks-container');
 * ```
 */
export function lauchFireworks(element) {
    const container = document.querySelector(element);
    const fireworks = new Fireworks(container, {
        autoresize: false,
        rocketsPoint: {
            min: 50,
            max: 50
        },
        hue: { min: 0, max: 345 },
        delay: { min: 15, max: 30 },
        speed: 2,
        acceleration: 1.05,
        friction: 0.98,
        gravity: 1.5,
        particles: 50,
        trace: 3,
        traceSpeed: 10,
    });
    fireworks.start();
    // After 2 seconds, we set intensity to 0. It hides the firework
    setTimeout(() => {
        fireworks.updateOptions({
            intensity: 0,
        });
        // After 3 seconds more, we stop the firework
        setTimeout(() => {
            fireworks.stop();
        }, 3000);
    }, 2000);
}

export function placePieces(fen, noLastMove, chess) {
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
        'r': 'b-rook',
        'n': 'b-knight',
        'b': 'b-bishop',
        'q': 'b-queen',
        'k': 'b-king',
        'p': 'b-pawn',
        'R': 'w-rook',
        'N': 'w-knight',
        'B': 'w-bishop',
        'Q': 'w-queen',
        'K': 'w-king',
        'P': 'w-pawn',
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
            if ($.inArray(count[j], ['r', 'n', 'b', 'q', 'k', 'p', 'R', 'N', 'B', 'Q', 'K', 'P']) !== -1) {
                chessboard[column[columnKey] + line] = piecesLabel[count[j]];
                columnKey += 1;
            } else {
                columnKey += parseInt(count[j]);
            }
        }
        line--;
    }

    console.log(chessboard);

    let src = null;
    var color = null;
    for (var k in chessboard) {
        src = PIECESIMGURL;
        src = src.replace("playerColor-chessboard", chessboard[k]);

        color = 'w';
        if (chessboard[k].indexOf('w-') == -1){
            color = 'b';
        }
        $('#' + k).append('<img class="piece ' + color + '" src="' + src + '" alt>');
    }

    // Set in green color the last move
    if ((typeof noLastMove === 'undefined' || noLastMove !== true) && typeof chess !== 'undefined') {
        let historyVerbose = chess.history({verbose: true});
        let lastMoveHistory = historyVerbose[historyVerbose.length - 1];
        $('.last-move').removeClass('last-move');
        if (typeof lastMoveHistory !== 'undefined') {
            $('#' + lastMoveHistory.from).addClass('last-move');
            $('#' + lastMoveHistory.to).addClass('last-move');
        }
    }
}

export function getKingPosition(fen, color) {
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
                if ((color === 'w' && count[j] === 'K') || (color === 'b' && count[j] === 'k')) {
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

export function promotionPiece(callback) {
    $('#promotion-modal').modal('show');
    $('#promotion-modal .promotion-piece-button').on('click', function() {
        var piece = $(this).attr('id');
        $('#promotion-modal').modal('hide');
        callback(piece);
    });
}
