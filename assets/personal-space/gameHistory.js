import $ from 'jquery';
import { Chess } from 'chess.js';
require('bootstrap');

$(function() {
    for (let i in GAMESLIST) {
        placePieces(GAMESLIST[i].id, GAMESLIST[i].fen, GAMESLIST[i].pgn);
    }

    function placePieces(idGame, fen, pgn) {
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

        let src = null;
        var color = null;
        for (var k in chessboard) {
            src = PIECESIMGURL;
            src = src.replace("playerColor-chessboard", chessboard[k]);

            color = 'w';
            if (chessboard[k].indexOf('w-') == -1){
                color = 'b';
            }
            $('#' + idGame + '-' + k).append('<img class="piece ' + color + '" src="' + src + '" alt>');
        }

        // Set in green color the last move
        const chess = new Chess(fen);
        if (pgn !== null) {
            chess.loadPgn(pgn);

            let chessHistory = chess.history({verbose: true});
            let lastMove = chessHistory[chessHistory.length - 1];
            if (typeof lastMove !== 'undefined' && typeof lastMove.from !== 'undefined' && typeof lastMove.to !== 'undefined') {
                $('#' + idGame + '-' + lastMove.from).addClass('last-move');
                $('#' + idGame + '-' + lastMove.to).addClass('last-move');
            }
        }
    }
});
