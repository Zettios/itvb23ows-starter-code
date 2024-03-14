from flask import Flask, request, jsonify
import vidstige.hive

app = Flask(__name__)


def char_to_tile(char):
    return {
        'Q': vidstige.hive.queen,
        'S': vidstige.hive.spider,
        'B': vidstige.hive.beetle,
        'A': vidstige.hive.ant,
        'G': vidstige.hive.grasshopper
    }[char[0].upper()]


def tile_to_char(tile):
    return str(tile)[0].upper()


def coord_to_string(pqr):
    return f"{pqr[0]},{pqr[1]}"


def string_to_coord(pq):
    p, q = list(map(int, pq.split(',')))[:2]
    return p, q, -p + -q


@app.route('/', methods=("POST",))
def ai_move():
    state = vidstige.hive.State()
    data = request.json
    state.move_number = int(data["move_number"])
    for i, ply in enumerate(data["hand"]):
        if len(ply):
            for k, v in ply.items():
                state.players[i].hand[char_to_tile(k)] = v
    if len(data["board"]):
        for pq, tiles in data["board"].items():
            for player, tile in tiles:
                state.grid[string_to_coord(pq)] = state.players[player], char_to_tile(tile)

    depth = 3
    inf = 2 ** 64

    move = vidstige.hive.minmax(state, state.player(), depth, -inf, inf)[0]
    if move is None or move[0] == 'nothing':
        return jsonify('pass', None, None)
    elif move[0] == 'place':
        return jsonify('play', tile_to_char(move[1]), coord_to_string(move[2]))
    elif move[0] == 'move':
        return jsonify('move', coord_to_string(move[1]), coord_to_string(move[2]))


if __name__ == '__main__':
    app.run()
