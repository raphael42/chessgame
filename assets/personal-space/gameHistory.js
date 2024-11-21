import $ from 'jquery';
require('bootstrap');

$(function() {
    console.log(GAMESLIST);
    for (let i in GAMESLIST) {
        placePieces(GAMESLIST[i].id, GAMESLIST[i].fen, GAMESLIST[i].pgn);
    }

    function placePieces(idGame, fen, pgn) {
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

            color = 'white';
            if (chessboard[k].indexOf('white') == -1){
                color = 'black';
            }
            $('#' + idGame + '-' + k).append('<img class="piece ' + color + '" src="' + src + '" alt>');
        }

        // Set in green color the last move
        if (typeof noLastMove === 'undefined' || noLastMove !== true) {

        }
    }
});
