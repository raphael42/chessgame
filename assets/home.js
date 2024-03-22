import $ from 'jquery';
require('bootstrap');

const socket = new WebSocket('ws://localhost:3001/home');

$(function() {
    socket.addEventListener('open', function(e) {
        console.log('open', e);
    });

    socket.addEventListener('message', function(e) {
        var socketMessage = JSON.parse(e.data);

        console.log(socketMessage);

        // When arriving in homepage, a first websocket hit with all waiting games is made, get it here
        if (typeof socketMessage.method !== 'undefined' && socketMessage.method === 'home_all_games') {
            for (let i in socketMessage.arrGames) {
                let html = '' +
                '<tr class="join-game" data-id="' + socketMessage.arrGames[i].id + '" data-url="' + socketMessage.arrGames[i].url + '">' +
                    '<td>' + socketMessage.arrGames[i].color + '</td>' +
                    '<td>' + (socketMessage.arrGames[i].time / 60) + ' + ' + socketMessage.arrGames[i].increment + '</td>' +
                    '<td>Amical</td>' +
                '</tr>';

                $('.tbody-waiting-games').append(html);
            }
        }

        // When a random game is created, we get the info here to update the table
        if (typeof socketMessage.method !== 'undefined' && socketMessage.method === 'new_game') {
            let html = '' +
            '<tr class="join-game" data-id="' + socketMessage.id + '" data-url="' + socketMessage.url + '">' +
                '<td>' + socketMessage.color + '</td>' +
                '<td>' + (socketMessage.time / 60) + ' + ' + socketMessage.increment + '</td>' +
                '<td>Amical</td>' +
            '</tr>';

            $('.tbody-waiting-games').append(html);
        }

        // When a random game find a player, remove it from the list
        if (typeof socketMessage.method !== 'undefined' && socketMessage.method === 'remove_game') {
            $('.tbody-waiting-games').find('[data-id="' + socketMessage.id + '"]').remove();
        }
    });

    socket.addEventListener('close', function(e) {
        console.log('close', e);
    });

    socket.addEventListener('error', function(e) {
        console.log('error', e);
    });

    // BOF fill the show chessboard
    var fenSplit = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR';
    var fenSplitPieces = fenSplit.split('/');
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
        if (chessboard[k].indexOf('white') == -1) {
            color = 'black';
        }
        $('#' + k).html('<img class="piece ' + color + '" src="' + src + '" alt>');
    }
    // EOF fill the show chessboard

    // Click on menu btn create Game
    let url = new URL(window.location.href);
    if (url.searchParams.get('game') === 'create-random-game') {
        $('#game-random-modal').modal('show');
    }
    if (url.searchParams.get('game') === 'create-friend-game') {
        $('#game-with-friend-modal').modal('show');
    }

    $('.tbody-waiting-games').off().on('click', '.join-game', function() {
        // Element is disabled, return and do not redirect
        if ($(this).hasClass('disabled')) {
            return;
        }

        let gameUrl = GAMEURL.replace('TOREPLACE', $(this).data('url'))

        window.location.href = gameUrl;
        return;
    });
});
