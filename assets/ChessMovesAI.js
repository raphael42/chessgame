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

var turn = null;
placePieces(FEN, false, chess);

calculateMaterial(FEN, PLAYERCOLOR);

var piecesAiRanking = {
    'p': 10,
    'n': 30,
    'b': 30,
    'r': 50,
    'q': 90,
    'k': 900,
};

var whitePiecePositionRanking = {
    'p': {
        'a8':  0.0, 'b8':  0.0, 'c8':  0.0, 'd8':  0.0, 'e8':  0.0, 'f8':  0.0, 'g8':  0.0, 'h8':  0.0,
        'a7':  5.0, 'b7':  5.0, 'c7':  5.0, 'd7':  5.0, 'e7':  5.0, 'f7':  5.0, 'g7':  5.0, 'h7':  5.0,
        'a6':  1.0, 'b6':  1.0, 'c6':  2.0, 'd6':  3.0, 'e6':  3.0, 'f6':  2.0, 'g6':  1.0, 'h6':  1.0,
        'a5':  0.5, 'b5':  0.5, 'c5':  1.0, 'd5':  2.5, 'e5':  2.5, 'f5':  1.0, 'g5':  0.5, 'h5':  0.5,
        'a4':  0.0, 'b4':  0.0, 'c4':  0.0, 'd4':  2.0, 'e4':  2.0, 'f4':  0.0, 'g4':  0.0, 'h4':  0.0,
        'a3':  0.5, 'b3': -0.5, 'c3': -1.0, 'd3':  0.0, 'e3':  0.0, 'f3': -1.0, 'g3': -0.5, 'h3':  0.5,
        'a2':  0.5, 'b2':  1.0, 'c2':  1.0, 'd2': -2.0, 'e2': -2.0, 'f2':  1.0, 'g2':  1.0, 'h2':  0.5,
        'a1':  0.0, 'b1':  0.0, 'c1':  0.0, 'd1':  0.0, 'e1':  0.0, 'f1':  0.0, 'g1':  0.0, 'h1':  0.0,
    },
    'n': {
        'a8': -5.0, 'b8': -4.0, 'c8': -3.0, 'd8': -3.0, 'e8': -3.0, 'f8': -3.0, 'g8': -4.0, 'h8': -5.0,
        'a7': -4.0, 'b7': -2.0, 'c7':  0.0, 'd7':  0.0, 'e7':  0.0, 'f7':  0.0, 'g7': -2.0, 'h7': -4.0,
        'a6': -3.0, 'b6':  0.0, 'c6':  1.0, 'd6':  1.5, 'e6':  1.5, 'f6':  1.0, 'g6':  0.0, 'h6': -3.0,
        'a5': -3.0, 'b5':  0.5, 'c5':  1.5, 'd5':  2.0, 'e5':  2.0, 'f5':  1.5, 'g5':  0.5, 'h5': -3.0,
        'a4': -3.0, 'b4':  0.0, 'c4':  1.5, 'd4':  2.0, 'e4':  2.0, 'f4':  1.5, 'g4':  0.0, 'h4': -3.0,
        'a3': -3.0, 'b3':  0.5, 'c3':  1.0, 'd3':  1.5, 'e3':  1.5, 'f3':  1.0, 'g3':  0.5, 'h3': -3.0,
        'a2': -4.0, 'b2': -2.0, 'c2':  0.0, 'd2':  0.5, 'e2':  0.5, 'f2':  0.0, 'g2': -2.0, 'h2': -4.0,
        'a1': -5.0, 'b1': -4.0, 'c1': -3.0, 'd1': -3.0, 'e1': -3.0, 'f1': -3.0, 'g1': -4.0, 'h1': -5.0,
    },
    'b': {
        'a8': -2.0, 'b8': -1.0, 'c8': -1.0, 'd8': -1.0, 'e8': -1.0, 'f8': -1.0, 'g8': -1.0, 'h8': -2.0,
        'a7': -1.0, 'b7':  0.0, 'c7':  0.0, 'd7':  0.0, 'e7':  0.0, 'f7':  0.0, 'g7':  0.0, 'h7': -1.0,
        'a6': -1.0, 'b6':  0.0, 'c6':  0.5, 'd6':  1.0, 'e6':  1.0, 'f6':  0.5, 'g6':  0.0, 'h6': -1.0,
        'a5': -1.0, 'b5':  0.5, 'c5':  0.5, 'd5':  1.0, 'e5':  1.0, 'f5':  0.5, 'g5':  0.5, 'h5': -1.0,
        'a4': -1.0, 'b4':  0.0, 'c4':  1.0, 'd4':  1.0, 'e4':  1.0, 'f4':  1.0, 'g4':  0.0, 'h4': -1.0,
        'a3': -1.0, 'b3':  1.0, 'c3':  1.0, 'd3':  1.0, 'e3':  1.0, 'f3':  1.0, 'g3':  1.0, 'h3': -1.0,
        'a2': -1.0, 'b2':  0.5, 'c2':  0.0, 'd2':  0.0, 'e2':  0.0, 'f2':  0.0, 'g2':  0.5, 'h2': -1.0,
        'a1': -2.0, 'b1': -1.0, 'c1': -1.0, 'd1': -1.0, 'e1': -1.0, 'f1': -1.0, 'g1': -1.0, 'h1': -2.0,
    },
    'r': {
        'a8':  0.0, 'b8':  0.0, 'c8':  0.0, 'd8':  0.0, 'e8':  0.0, 'f8':  0.0, 'g8':  0.0, 'h8':  0.0,
        'a7':  0.5, 'b7':  1.0, 'c7':  1.0, 'd7':  1.0, 'e7':  1.0, 'f7':  1.0, 'g7':  1.0, 'h7':  0.5,
        'a6': -0.5, 'b6':  0.0, 'c6':  0.0, 'd6':  0.0, 'e6':  0.0, 'f6':  0.0, 'g6':  0.0, 'h6': -0.5,
        'a5': -0.5, 'b5':  0.0, 'c5':  0.0, 'd5':  0.0, 'e5':  0.0, 'f5':  0.0, 'g5':  0.0, 'h5': -0.5,
        'a4': -0.5, 'b4':  0.0, 'c4':  0.0, 'd4':  0.0, 'e4':  0.0, 'f4':  0.0, 'g4':  0.0, 'h4': -0.5,
        'a3': -0.5, 'b3':  0.0, 'c3':  0.0, 'd3':  0.0, 'e3':  0.0, 'f3':  0.0, 'g3':  0.0, 'h3': -0.5,
        'a2': -0.5, 'b2':  0.0, 'c2':  0.0, 'd2':  0.0, 'e2':  0.0, 'f2':  0.0, 'g2':  0.0, 'h2': -0.5,
        'a1':  0.0, 'b1':  0.0, 'c1':  0.0, 'd1':  0.5, 'e1':  0.5, 'f1':  0.0, 'g1':  0.0, 'h1':  0.0,
    },
    'q': {
        'a8': -2.0, 'b8': -1.0, 'c8': -1.0, 'd8': -0.5, 'e8': -0.5, 'f8': -1.0, 'g8': -1.0, 'h8': -2.0,
        'a7': -1.0, 'b7':  0.0, 'c7':  0.0, 'd7':  0.0, 'e7':  0.0, 'f7':  0.0, 'g7':  0.0, 'h7': -1.0,
        'a6': -1.0, 'b6':  0.0, 'c6':  0.5, 'd6':  0.5, 'e6':  0.5, 'f6':  0.5, 'g6':  0.0, 'h6': -1.0,
        'a5': -0.5, 'b5':  0.0, 'c5':  0.5, 'd5':  0.5, 'e5':  0.5, 'f5':  0.5, 'g5':  0.0, 'h5': -0.5,
        'a4':  0.0, 'b4':  0.0, 'c4':  0.5, 'd4':  0.5, 'e4':  0.5, 'f4':  0.5, 'g4':  0.0, 'h4': -0.5,
        'a3': -1.0, 'b3':  0.5, 'c3':  0.5, 'd3':  0.5, 'e3':  0.5, 'f3':  0.5, 'g3':  0.0, 'h3': -1.0,
        'a2': -1.0, 'b2':  0.0, 'c2':  0.5, 'd2':  0.0, 'e2':  0.0, 'f2':  0.0, 'g2':  0.0, 'h2': -1.0,
        'a1': -2.0, 'b1': -1.0, 'c1': -1.0, 'd1': -0.5, 'e1': -0.5, 'f1': -1.0, 'g1': -1.0, 'h1': -2.0,
    },
    'k': {
        'a8': -3.0, 'b8': -4.0, 'c8': -4.0, 'd8': -5.0, 'e8': -5.0, 'f8': -4.0, 'g8': -4.0, 'h8': -3.0,
        'a7': -3.0, 'b7': -4.0, 'c7': -4.0, 'd7': -5.0, 'e7': -5.0, 'f7': -4.0, 'g7': -4.0, 'h7': -3.0,
        'a6': -3.0, 'b6': -4.0, 'c6': -4.0, 'd6': -5.0, 'e6': -5.0, 'f6': -4.0, 'g6': -4.0, 'h6': -3.0,
        'a5': -3.0, 'b5': -4.0, 'c5': -4.0, 'd5': -5.0, 'e5': -5.0, 'f5': -4.0, 'g5': -4.0, 'h5': -3.0,
        'a4': -2.0, 'b4': -3.0, 'c4': -3.0, 'd4': -4.0, 'e4': -4.0, 'f4': -3.0, 'g4': -3.0, 'h4': -2.0,
        'a3': -1.0, 'b3': -2.0, 'c3': -2.0, 'd3': -2.0, 'e3': -2.0, 'f3': -2.0, 'g3': -2.0, 'h3': -1.0,
        'a2':  2.0, 'b2':  2.0, 'c2':  0.0, 'd2':  0.0, 'e2':  0.0, 'f2':  0.0, 'g2':  2.0, 'h2':  2.0,
        'a1':  2.0, 'b1':  3.0, 'c1':  1.0, 'd1':  0.0, 'e1':  0.0, 'f1':  1.0, 'g1':  3.0, 'h1':  2.0,
    },
};

var blackPiecePositionRanking = {
    'p': {
        'a1':  0.0, 'b1':  0.0, 'c1':  0.0, 'd1':  0.0, 'e1':  0.0, 'f1':  0.0, 'g1':  0.0, 'h1':  0.0,
        'a2':  5.0, 'b2':  5.0, 'c2':  5.0, 'd2':  5.0, 'e2':  5.0, 'f2':  5.0, 'g2':  5.0, 'h2':  5.0,
        'a3':  1.0, 'b3':  1.0, 'c3':  2.0, 'd3':  3.0, 'e3':  3.0, 'f3':  2.0, 'g3':  1.0, 'h3':  1.0,
        'a4':  0.5, 'b4':  0.5, 'c4':  1.0, 'd4':  2.5, 'e4':  2.5, 'f4':  1.0, 'g4':  0.5, 'h4':  0.5,
        'a5':  0.0, 'b5':  0.0, 'c5':  0.0, 'd5':  2.0, 'e5':  2.0, 'f5':  0.0, 'g5':  0.0, 'h5':  0.0,
        'a6':  0.5, 'b6': -0.5, 'c6': -1.0, 'd6':  0.0, 'e6':  0.0, 'f6': -1.0, 'g6': -0.5, 'h6':  0.5,
        'a7':  0.5, 'b7':  1.0, 'c7':  1.0, 'd7': -2.0, 'e7': -2.0, 'f7':  1.0, 'g7':  1.0, 'h7':  0.5,
        'a8':  0.0, 'b8':  0.0, 'c8':  0.0, 'd8':  0.0, 'e8':  0.0, 'f8':  0.0, 'g8':  0.0, 'h8':  0.0
    },
    'n': {
        'a1': -5.0, 'b1': -4.0, 'c1': -3.0, 'd1': -3.0, 'e1': -3.0, 'f1': -3.0, 'g1': -4.0, 'h1': -5.0,
        'a2': -4.0, 'b2': -2.0, 'c2':  0.0, 'd2':  0.0, 'e2':  0.0, 'f2':  0.0, 'g2': -2.0, 'h2': -4.0,
        'a3': -3.0, 'b3':  0.0, 'c3':  1.0, 'd3':  1.5, 'e3':  1.5, 'f3':  1.0, 'g3':  0.0, 'h3': -3.0,
        'a4': -3.0, 'b4':  0.5, 'c4':  1.5, 'd4':  2.0, 'e4':  2.0, 'f4':  1.5, 'g4':  0.5, 'h4': -3.0,
        'a5': -3.0, 'b5':  0.0, 'c5':  1.5, 'd5':  2.0, 'e5':  2.0, 'f5':  1.5, 'g5':  0.0, 'h5': -3.0,
        'a6': -3.0, 'b6':  0.5, 'c6':  1.0, 'd6':  1.5, 'e6':  1.5, 'f6':  1.0, 'g6':  0.5, 'h6': -3.0,
        'a7': -4.0, 'b7': -2.0, 'c7':  0.0, 'd7':  0.5, 'e7':  0.5, 'f7':  0.0, 'g7': -2.0, 'h7': -4.0,
        'a8': -5.0, 'b8': -4.0, 'c8': -3.0, 'd8': -3.0, 'e8': -3.0, 'f8': -3.0, 'g8': -4.0, 'h8': -5.0
    },
    'b': {
        'a1': -2.0, 'b1': -1.0, 'c1': -1.0, 'd1': -1.0, 'e1': -1.0, 'f1': -1.0, 'g1': -1.0, 'h1': -2.0,
        'a2': -1.0, 'b2':  0.5, 'c2':  0.0, 'd2':  0.0, 'e2':  0.0, 'f2':  0.0, 'g2':  0.5, 'h2': -1.0,
        'a3': -1.0, 'b3':  1.0, 'c3':  1.0, 'd3':  1.0, 'e3':  1.0, 'f3':  1.0, 'g3':  1.0, 'h3': -1.0,
        'a4': -1.0, 'b4':  0.0, 'c4':  1.0, 'd4':  1.0, 'e4':  1.0, 'f4':  1.0, 'g4':  0.0, 'h4': -1.0,
        'a5': -1.0, 'b5':  0.5, 'c5':  0.5, 'd5':  1.0, 'e5':  1.0, 'f5':  0.5, 'g5':  0.5, 'h5': -1.0,
        'a6': -1.0, 'b6':  0.0, 'c6':  0.5, 'd6':  1.0, 'e6':  1.0, 'f6':  0.5, 'g6':  0.0, 'h6': -1.0,
        'a7': -1.0, 'b7':  0.0, 'c7':  0.0, 'd7':  0.0, 'e7':  0.0, 'f7':  0.0, 'g7':  0.0, 'h7': -1.0,
        'a8': -2.0, 'b8': -1.0, 'c8': -1.0, 'd8': -1.0, 'e8': -1.0, 'f8': -1.0, 'g8': -1.0, 'h8': -2.0
    },
    'r': {
        'a1':  0.0, 'b1':  0.0, 'c1':  0.0, 'd1':  0.5, 'e1':  0.5, 'f1':  0.0, 'g1':  0.0, 'h1':  0.0,
        'a2': -0.5, 'b2':  0.0, 'c2':  0.0, 'd2':  0.0, 'e2':  0.0, 'f2':  0.0, 'g2':  0.0, 'h2': -0.5,
        'a3': -0.5, 'b3':  0.0, 'c3':  0.0, 'd3':  0.0, 'e3':  0.0, 'f3':  0.0, 'g3':  0.0, 'h3': -0.5,
        'a4': -0.5, 'b4':  0.0, 'c4':  0.0, 'd4':  0.0, 'e4':  0.0, 'f4':  0.0, 'g4':  0.0, 'h4': -0.5,
        'a5': -0.5, 'b5':  0.0, 'c5':  0.0, 'd5':  0.0, 'e5':  0.0, 'f5':  0.0, 'g5':  0.0, 'h5': -0.5,
        'a6': -0.5, 'b6':  0.0, 'c6':  0.0, 'd6':  0.0, 'e6':  0.0, 'f6':  0.0, 'g6':  0.0, 'h6': -0.5,
        'a7':  0.5, 'b7':  1.0, 'c7':  1.0, 'd7':  1.0, 'e7':  1.0, 'f7':  1.0, 'g7':  1.0, 'h7':  0.5,
        'a8':  0.0, 'b8':  0.0, 'c8':  0.0, 'd8':  0.0, 'e8':  0.0, 'f8':  0.0, 'g8':  0.0, 'h8':  0.0
    },
    'q': {
        'a1': -2.0, 'b1': -1.0, 'c1': -1.0, 'd1': -0.5, 'e1': -0.5, 'f1': -1.0, 'g1': -1.0, 'h1': -2.0,
        'a2': -1.0, 'b2':  0.0, 'c2':  0.5, 'd2':  0.0, 'e2':  0.0, 'f2':  0.0, 'g2':  0.0, 'h2': -1.0,
        'a3': -1.0, 'b3':  0.5, 'c3':  0.5, 'd3':  0.5, 'e3':  0.5, 'f3':  0.5, 'g3':  0.0, 'h3': -1.0,
        'a4':  0.0, 'b4':  0.0, 'c4':  0.5, 'd4':  0.5, 'e4':  0.5, 'f4':  0.5, 'g4':  0.0, 'h4': -0.5,
        'a5': -0.5, 'b5':  0.0, 'c5':  0.5, 'd5':  0.5, 'e5':  0.5, 'f5':  0.5, 'g5':  0.0, 'h5': -0.5,
        'a6': -1.0, 'b6':  0.0, 'c6':  0.5, 'd6':  0.5, 'e6':  0.5, 'f6':  0.5, 'g6':  0.0, 'h6': -1.0,
        'a7': -1.0, 'b7':  0.0, 'c7':  0.0, 'd7':  0.0, 'e7':  0.0, 'f7':  0.0, 'g7':  0.0, 'h7': -1.0,
        'a8': -2.0, 'b8': -1.0, 'c8': -1.0, 'd8': -0.5, 'e8': -0.5, 'f8': -1.0, 'g8': -1.0, 'h8': -2.0
    },
    'k': {
        'a1': -3.0, 'b1': -4.0, 'c1': -4.0, 'd1': -5.0, 'e1': -5.0, 'f1': -4.0, 'g1': -4.0, 'h1': -3.0,
        'a2': -3.0, 'b2': -4.0, 'c2': -4.0, 'd2': -5.0, 'e2': -5.0, 'f2': -4.0, 'g2': -4.0, 'h2': -3.0,
        'a3': -3.0, 'b3': -4.0, 'c3': -4.0, 'd3': -5.0, 'e3': -5.0, 'f3': -4.0, 'g3': -4.0, 'h3': -3.0,
        'a4': -3.0, 'b4': -4.0, 'c4': -4.0, 'd4': -5.0, 'e4': -5.0, 'f4': -4.0, 'g4': -4.0, 'h4': -3.0,
        'a5': -2.0, 'b5': -3.0, 'c5': -3.0, 'd5': -4.0, 'e5': -4.0, 'f5': -3.0, 'g5': -3.0, 'h5': -2.0,
        'a6': -1.0, 'b6': -2.0, 'c6': -2.0, 'd6': -2.0, 'e6': -2.0, 'f6': -2.0, 'g6': -2.0, 'h6': -1.0,
        'a7':  2.0, 'b7':  2.0, 'c7':  0.0, 'd7':  0.0, 'e7':  0.0, 'f7':  0.0, 'g7':  2.0, 'h7':  2.0,
        'a8':  2.0, 'b8':  3.0, 'c8':  1.0, 'd8':  0.0, 'e8':  0.0, 'f8':  1.0, 'g8':  3.0, 'h8':  2.0
    },
}

$(function() {
    if (chess.inCheck() === true) {
        let kingposition = getKingPosition(FEN, chess.turn());
        $('#' + kingposition).addClass('in-check');
    }

    if (GAMESTATUS !== 'finished') {
        // It's white turn
        if (chess.turn() === 'w') {
            if (PLAYERCOLOR === 'w') { // Player has white, so it's his turn
                $('#title').html('C\'est votre tour ! | ' + SERVERNAME);
            } else { // Player has black, so it's the AI turn
                $('#title').html('En attente de l\'adversaire | ' + SERVERNAME);
                AiPlay();
            }

        // It's black turn
        } else {
            if (PLAYERCOLOR === 'b') { // Player has black, so it's his turn
                $('#title').html('C\'est votre tour ! | ' + SERVERNAME);
            } else { // Player has white, so it's the AI turn
                $('#title').html('En attente de l\'adversaire | ' + SERVERNAME);
                AiPlay();
            }
        }
    }

    $('#board').off().on('click', '.chess-table', function() {
        console.log('cccc');
        // If player is watching history, disable the possibility to move
        if (HISTORYINVIEW) {
            return;
        }

        // If game is finished, disable moves
        if (GAMESTATUS === 'finished') {
            return;
        }

        // If the user is a spectator, disable moves
        // if (PLAYERTYPE === 'spectator') {
        //     return;
        // }

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

            if (squareFromInfos !== null && squareFromInfos['type'] === 'p' && ((PLAYERCOLOR === 'w' && squareFromInfos['color'] === 'w' && idFromLine === 7 && idToLine === 8) || (PLAYERCOLOR === 'b' && squareFromInfos['color'] === 'b' && idFromLine === 2 && idToLine === 1))) {
                promotionPiece(function(promotion) {
                    processMove(squareIdFrom, squareIdTo, promotion);
                });
            } else {
                processMove(squareIdFrom, squareIdTo, null);
            }
        }

        // Second click to premove in no piece square
        if ($('#board').find('.clicked-premove').length === 1 && !$(self).hasClass('clicked-premove')) {
            $(self).addClass('clicked-premove');
            $(self).data('premove', 2);
        }
    });

    // Set up draggable only if the game is not finished and the player is not a spectator
    // if (GAMESTATUS !== 'finished' && PLAYERTYPE !== 'spectator') {
    if (GAMESTATUS !== 'finished') {
        setupDraggable();
    }

    $('.chess-table').droppable({
        drop: function(ev, ui) {
            // If game is finished, disable moves
            if (GAMESTATUS === 'finished') {
                return;
            }

            // If the user is a spectator, disable moves
            // if (PLAYERTYPE === 'spectator') {
            //     return;
            // }

            // If player is watching history, disable the possibility to move
            if (HISTORYINVIEW) {
                return;
            }

            // Set back z-index to the element dropped
            $(ui.helper[0]).css('z-index', 2);

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
                        kingposition = getKingPosition(fen, 'b')
                    } else {
                        kingposition = getKingPosition(fen, 'w')
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
                }
                if (chessHistory[HISTORYINDEX].color === 'b') {
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
                    let kingposition = '';
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
        // if (PLAYERTYPE === 'spectator') {
        //     return;
        // }

        $('#resign-modal').modal('show');
        $('#confirm-resign-modal').off().on('click', function() {
            $('#resign-modal').modal('hide');

            if (PLAYERCOLOR === 'w') {
                gameIsOver('resign', PLAYERCOLOR, 'Black win ! White resign');
            } else {
                gameIsOver('resign', PLAYERCOLOR, 'White win ! Black resign');
            }
        });
    });

    $('#takeback').on('click', function() {
        // If game is finished, disable takeback
        if (GAMESTATUS === 'finished') {
            return;
        }

        // If the user is a spectator, disable takeback
        // if (PLAYERTYPE === 'spectator') {
        //     return;
        // }

        // Always revert 2 moves, the computer one, and the player one
        let undo = chess.undo();
        let fenSplit = undo.before.split(' ');
        let moveNumber = fenSplit[5];
        // If undo a white move, remove an history line
        if (undo.color === 'w') {
            $('.move-' + moveNumber).remove();
        } else { // If undo a black move, empty the move history square
            $('#move-san-b-' + moveNumber).empty();
        }

        let undo2 = chess.undo();
        let fenSplit2 = undo2.before.split(' ');
        let moveNumber2 = fenSplit2[5];
        // If undo a white move, remove an history line
        if (undo2.color === 'w') {
            $('.move-' + moveNumber2).remove();
        } else { // If undo a black move, empty the move history square
            $('#move-san-b-' + moveNumber2).empty();
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

        const data = {
            'game-url': GAMEURL,
            'fen': chess.fen(),
            'pgn': chess.pgn(),
        };
        $.ajax({
            url: AJAXAITAKEBACKURL,
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
            // if (PLAYERTYPE === 'spectator') {
            //     return;
            // }

            // Remove the previous clicked class and possible-move class from all squares
            $('.chess-table.clicked').removeClass('clicked');
            $('.chess-table.possible-move').each(function() {
                $(this).removeClass('possible-move');
            });

            // Set highter z-index to the element dragged
            $(ui.helper[0]).css('z-index', 3);

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
            let kingposition = '';
            if (moving.color === 'w') {
                kingposition = getKingPosition(chess.fen(), 'b');
            } else {
                kingposition = getKingPosition(chess.fen(), 'w');
            }
            $('#' + kingposition).addClass('in-check');
        }

        if (wasInCheck) {
            $('.in-check').removeClass('in-check');
        }
        if (moving.flags === 'k') { // king side castelling
            if (moving.color === 'w') {
                let pieceImg = $('#h1').find('img');
                $('#h1 img').remove();
                $('#f1').append(pieceImg);
                setupDraggable('#f1 img');
            } else if (moving.color === 'b') {
                let pieceImg = $('#h8').find('img');
                $('#h8 img').remove();
                $('#f8').append(pieceImg);
                setupDraggable('#f8 img');
            }
        } else if (moving.flags === 'q') { // queen side castelling
            if (moving.color === 'w') {
                let pieceImg = $('#a1').find('img');
                $('#a1 img').remove();
                $('#d1').append(pieceImg);
                setupDraggable('#d1 img');
            } else if (moving.color === 'b') {
                let pieceImg = $('#a8').find('img');
                $('#a8 img').remove();
                $('#d8').append(pieceImg);
                setupDraggable('#d8 img');
            }
        } else if (moving.flags === 'e') { // en passant capture
            if (moving.color === 'w') {
                var tmp = (moving.to).split('');
                var idPawnCatured = tmp[0] + (parseInt(tmp[1]) - 1);
            } else if (moving.color === 'b') {
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
            src = src.replace('playerColor', moving.color);
            $('#' + squareIdTo).append('<img class="piece ' + moving.color + '" src="' + src + '" alt>');
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

        $('#playerturn-start').addClass('d-none');

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
                    lastMoveHistory['message'] = 'White win ! Black is checkmate'
                    gameIsOver(lastMoveHistory['gameReason'], 'w', lastMoveHistory['message']);
                } else {
                    lastMoveHistory['message'] = 'Black win ! White is checkmate';
                    gameIsOver(lastMoveHistory['gameReason'], 'b', lastMoveHistory['message']);
                }
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
                gameIsOver(lastMoveHistory['gameReason'], 'd', lastMoveHistory['message']);
            }
        }

        switchTurn();

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

        // Computer play
        let playerTurn = chess.turn(); // Which player turn it is
        if (
            GAMESTATUS !== 'finished' &&
            ((PLAYERCOLOR === 'w' && playerTurn === 'b') ||
            (PLAYERCOLOR === 'b' && playerTurn === 'w'))
        ) { // And is not finished, it's not the player turn, then make an AI move
            AiPlay();
        }

        lastMoveHistory['game-url'] = GAMEURL;
        $.ajax({
            url: AJAXAIONEMOVEURL,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(lastMoveHistory),
            error: function(xhr, status, error) {
                console.log(status + ' : ' + error);
                var errorMessage = xhr.status + ': ' + xhr.statusText
                console.log('Error - ' + errorMessage);
            },
            success: function(data) {
                console.log(data);
            }
        });

        calculateMaterial(lastMoveHistory.after,PLAYERCOLOR);

        return true;
    }

    $('.chess-table.clicked-premove').each(function() {
        $(this).removeClass('clicked-premove');
    });

    return false;
}

function AiPlay() {
    $('#takeback').prop('disabled', true);

    setTimeout(() => {
        if (GAMESTATUS === 'finished') {
            return;
        }

        let aiPossibleMoves = chess.moves({
            verbose: true
        });

        var bestAiMove = [];
        var bestSavePlayerScore = null;

        // Test all the AI possible moves
        firstLoop:for (let i in aiPossibleMoves) {
            // Create new Chess object for the ai move
            const chessAiMove = new Chess(chess.fen());

            // Process one AI test move
            chessAiMove.move({
                from: aiPossibleMoves[i].from,
                to: aiPossibleMoves[i].to,
                promotion: (typeof aiPossibleMoves[i].promotion !== 'undefined') ? 'q' : null, // Promotion always queen to make it simple
            });

            // After one AI move, try again all the player possible moves
            let playerPossibleMoves = chessAiMove.moves({
                verbose: true
            });

            let bestPlayerScore = null;
            for (let j in playerPossibleMoves) {
                const chessPlayerMove = new Chess(chessAiMove.fen());
                chessPlayerMove.move({
                    from: playerPossibleMoves[j].from,
                    to: playerPossibleMoves[j].to,
                    promotion: (typeof playerPossibleMoves[j].promotion !== 'undefined') ? 'q' : null, // Promotion always queen to make it simple
                });

                let playerScoreAfterMove = null;
                if (AILEVEL === '1') {
                    playerScoreAfterMove = calculateScoreNoSquare(chessPlayerMove.fen(), PLAYERCOLOR.charAt(0));
                } else {
                    playerScoreAfterMove = calculateScore(chessPlayerMove.fen(), PLAYERCOLOR.charAt(0));
                }

                // CheckMate after the Player move, set super hight score to not choose it later
                if (chessPlayerMove.isCheckmate() === true) {
                    playerScoreAfterMove = 900;
                }

                // If the move the player can perform has highter score than the one actually saved, the move for the AI is worst so go next to make the script faster
                if (bestSavePlayerScore !== null && bestSavePlayerScore < playerScoreAfterMove) {
                    continue firstLoop;
                }

                // Save the best score the Player can get after the AI move
                if (bestPlayerScore === null || playerScoreAfterMove > bestPlayerScore) {
                    bestPlayerScore = playerScoreAfterMove;
                }
            }

            // CheckMate after the AI move, set super low score to choose it
            if (chessAiMove.isCheckmate() === true) {
                bestPlayerScore = -900;
            }

            if (bestAiMove.length === 0) {
                bestAiMove.push({
                    'from': aiPossibleMoves[i].from,
                    'to': aiPossibleMoves[i].to,
                    'piece': aiPossibleMoves[i].piece,
                    'color': aiPossibleMoves[i].color,
                    'playerScore': bestPlayerScore,
                });
                bestSavePlayerScore = bestPlayerScore;
            } else { // Choose the worst "best score" the player can get after the AI move.
                // Player score saved is higther than this one, empty the array
                if (bestSavePlayerScore > bestPlayerScore) {
                    bestSavePlayerScore = bestPlayerScore;
                    bestAiMove = [];
                    bestAiMove.push({
                        'from': aiPossibleMoves[i].from,
                        'to': aiPossibleMoves[i].to,
                        'piece': aiPossibleMoves[i].piece,
                        'color': aiPossibleMoves[i].color,
                        'playerScore': bestPlayerScore,
                    });
                } else if (bestSavePlayerScore === bestPlayerScore) { // Score is equal, save it to the array to choose a random move after
                    bestAiMove.push({
                        'from': aiPossibleMoves[i].from,
                        'to': aiPossibleMoves[i].to,
                        'piece': aiPossibleMoves[i].piece,
                        'color': aiPossibleMoves[i].color,
                        'playerScore': bestPlayerScore,
                    });
                }
            }
        }

        var randomElement = bestAiMove[Math.floor(Math.random() * bestAiMove.length)];

        var tmp2 = (randomElement.from).split('');
        var idFromLine = parseInt(tmp2[1]);

        var tmp3 = (randomElement.to).split('');
        var idToLine = parseInt(tmp3[1]);

        if (randomElement.piece === 'p' && ((randomElement.color === 'w' && idFromLine === 7 && idToLine === 8) || (randomElement.color === 'b' && idFromLine === 2 && idToLine === 1))) {
            processMove(randomElement.from, randomElement.to, 'q'); // Always queen as promotion for now
        } else {
            processMove(randomElement.from, randomElement.to, null);
        }

        // If it's the first move, let the takeback disabled
        if (chess.moveNumber() > 1) {
            $('#takeback').prop('disabled', false);
        }
    }, '500');
}

function calculateScore(fen, color) {
    let fenSplit = fen.split(' ');
    let fenPieces = fenSplit[0];
    let fenLinesSplit = fenPieces.split('/');

    let column = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
    let columnKey = 0;
    let line = 8;

    let whiteScore = 0;
    let blackScore = 0;
    for (let i in fenLinesSplit) {
        let onePiece = fenLinesSplit[i].split('');
        columnKey = 0;
        for (let j in onePiece) {
            if ($.inArray(onePiece[j], ['r', 'n', 'b', 'q', 'k', 'p', 'R', 'N', 'B', 'Q', 'K', 'P']) !== -1) { // It's a piece
                if (onePiece[j] == onePiece[j].toUpperCase()) { // If uppsercase, it's a white piece
                    whiteScore += piecesAiRanking[onePiece[j].toLowerCase()] + whitePiecePositionRanking[(onePiece[j]).toLowerCase()][column[columnKey] + line];
                } else {// If not, it's a black piece
                    blackScore += piecesAiRanking[onePiece[j]] + blackPiecePositionRanking[(onePiece[j])][column[columnKey] + line];
                }
                columnKey += 1;
            } else { // It's empty square => a number
                columnKey += parseInt(onePiece[j]);
            }
        }
        line--;
    }

    if (color === 'w') {
        return whiteScore - blackScore
    }

    if (color === 'b') {
        return blackScore - whiteScore
    }

    return {
        'w': whiteScore - blackScore,
        'b': blackScore - whiteScore,
    };
}

function calculateScoreNoSquare(fen, color) {
    let fenSplit = fen.split(' ');
    let fenPieces = fenSplit[0];
    let fenPiecesSplit = fenPieces.split('');

    let whiteScore = 0;
    let blackScore = 0;
    for (let i in fenPiecesSplit) { // Each characters of the fen
        if (typeof piecesAiRanking[fenPiecesSplit[i]] !== 'undefined') { // Piece found in the ranking array, add to the black
            blackScore += piecesAiRanking[fenPiecesSplit[i]];
        } else if (typeof piecesAiRanking[fenPiecesSplit[i].toLowerCase()] !== 'undefined') { // Piece was not found previously, found now while lowercasing, it's an uppercase piece (white)
            whiteScore += piecesAiRanking[fenPiecesSplit[i].toLowerCase()];
        }
    }

    if (color === 'w') {
        return whiteScore - blackScore
    }

    if (color === 'b') {
        return blackScore - whiteScore
    }

    return {
        'w': whiteScore - blackScore,
        'b': blackScore - whiteScore,
    };
}

function switchTurn() {
    if (turn === 'player') {
        turn = 'opponent';
    } else {
        turn = 'player';
    }
}


function gameIsOver(reason, playerWinner, endReason) {
    GAMESTATUS = 'finished';

    $('.end-game-reason').html(endReason);
    $('.tchat').append('<div>' + endReason + '</div>');

    $('.piece.' + PLAYERCOLOR).draggable('destroy');

    // The player is the winner
    if (playerWinner === PLAYERCOLOR) {
        lauchFireworks('#fireworks-container');
    }

    const data = {
        'game-url': GAMEURL,
        'winner-color': playerWinner,
        'reason': reason,
        'fen': chess.fen(),
        'pgn': chess.pgn(),
    };

    $.ajax({
        url: AJAXAIENDGAMEURL,
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
            $('#renew-modal').modal('show');
        }
    });
}
