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

/**
 * Places chess pieces on the board based on the provided FEN string.
 *
 * @param {string} fen - The FEN (Forsyth-Edwards Notation) string representing the chessboard state.
 * @param {boolean} [noLastMove=false] - If true, the last move will not be highlighted.
 * @param {object} [chess] - An optional Chess.js instance to retrieve the move history for highlighting the last move.
 *
 * @description
 * This function parses the FEN string to determine the positions of the chess pieces
 * and places them on the board. It also highlights the last move if applicable.
 * The function assumes the presence of a DOM structure with elements representing
 * the chessboard squares, identified by their IDs (e.g., "a1", "b2").
 *
 * @example
 * // Example usage:
 * const fen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1";
 * placePieces(fen, false, chessInstance);
 *
 * @global
 * - Assumes the presence of a global variable `PIECESIMGURL` for the image URL template.
 * - Uses jQuery for DOM manipulation.
 */
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

/**
 * Retrieves the position of the king on a chessboard based on the FEN string and the specified color.
 *
 * @param {string} fen - The FEN (Forsyth-Edwards Notation) string representing the chessboard state.
 * @param {string} color - The color of the king to locate ('w' for white, 'b' for black).
 * @returns {string|null} The position of the king in algebraic notation (e.g., "e1"), or null if not found.
 */
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

/**
 * Displays a modal dialog for pawn promotion in a chess game and handles the selection of the promotion piece.
 *
 * @param {function(string): void} callback - A callback function that is invoked with the ID of the selected promotion piece.
 * The ID corresponds to the piece chosen by the user (e.g., 'queen', 'rook', 'bishop', 'knight').
 *
 * @example
 * promotionPiece(function(selectedPiece) {
 *     console.log('User selected:', selectedPiece);
 *     // Handle the selected piece (e.g., update the game state)
 * });
 */
export function promotionPiece(callback) {
    $('#promotion-modal').modal('show');
    $('#promotion-modal .promotion-piece-button').on('click', function() {
        var piece = $(this).attr('id');
        $('#promotion-modal').modal('hide');
        callback(piece);
    });
}


/**
 * Calculates the material score for a chess game based on the FEN string and updates the UI with the score.
 *
 * @param {string} fen - The FEN (Forsyth-Edwards Notation) string representing the current state of the chessboard.
 * @param {string} playerColor - The color of the player ('w' for white, 'b' for black).
 * @returns {Object} An object containing the material scores and advantages:
 *   - {number} white: The total material score for white.
 *   - {number} black: The total material score for black.
 *   - {number} advantageWhite: The material advantage for white (whiteScore - blackScore).
 *   - {number} advantageBlack: The material advantage for black (blackScore - whiteScore).
 *
 * @throws {ReferenceError} If the variable `piecesRanking` is not defined.
 * @throws {ReferenceError} If jQuery ($) is not available for DOM manipulation.
 *
 * @example
 * const fen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1";
 * const playerColor = 'w';
 * const result = calculateMaterial(fen, playerColor);
 * console.log(result);
 * // Output: { white: 39, black: 39, advantageWhite: 0, advantageBlack: 0 }
 */
export function calculateMaterial(fen, playerColor) {
    let whiteScore = 0;
    let blackScore = 0;

    const piecesRanking = {
        'p': 1,
        'n': 3,
        'b': 3,
        'r': 5,
        'q': 9,
        'k': 0,
    };

    const fenSplit = fen.split(' ');

    // Loop on each FEN caracters
    for (const char of fenSplit[0]) {
        // If slash found, go next
        if (char === '/') {
            continue;
        }

        // If number found (empty square), go next
        if (/[0-9]/.test(char)) {
            continue;
        }

        // Get value of piece
        const pieceValue = piecesRanking[char.toLowerCase()];

        // If it's cap, it's a white piece
        if (char === char.toUpperCase()) {
            whiteScore += pieceValue;
        } else {
            blackScore += pieceValue;
        }
    }

    let advantageWhite = whiteScore - blackScore;
    let advantageBlack = blackScore - whiteScore;

    if (advantageWhite > 0) {
        if (playerColor === 'w') {
            $('.score-player').html('+' + advantageWhite);
            $('.score-opponent').html('');
        } else {
            $('.score-opponent').html('+' + advantageWhite);
            $('.score-player').html('');
        }
    } else if (advantageBlack > 0) {
        if (playerColor === 'b') {
            $('.score-player').html('+' + advantageBlack);
            $('.score-opponent').html('');
        } else {
            $('.score-opponent').html('+' + advantageBlack);
            $('.score-player').html('');
        }
    } else {
        $('.score-player').html('');
        $('.score-opponent').html('');
    }

    return {
        white: whiteScore,
        black: blackScore,
        advantageWhite: whiteScore - blackScore,
        advantageBlack: blackScore - whiteScore,
    };
}
