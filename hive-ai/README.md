# ITVB23OWS Development Pipelines Hive AI

This repository contains an implementation of a Hive AI for the course ITVB23OWS Development
pipelines, which is part of the HBO-ICT program at Hanze University of Applied Sciences in
Groningen.

The implementation is based on the work of [Samuel Carlsson](https://github.com/vidstige) in the
Github repository [vidstige/hive](https://github.com/vidstige/hive), with minor modifications.

The application contains Python code and can be run using the built-in Flask server, which can
be started using the following command.

```
flask --app app run --debug
```

The AI can be called using HTTP at the root URL. The API expects a POST request with
a `Content-Type: application/json` header and a JSON payload. It will return a JSON response.

An example request is as follows.

```
{
    "move_number": 1
    "hand": [
        {"Q": 1, "B": 1, "A": 1, "S": 1, "G": 1},
        {"Q": 1, "B": 1, "A": 1, "S": 1, "G": 1},
    ],
    "board": {
        "0,0": [[0, "Q"], [1, "B"]],
        "-1,0": [[1, "A"]]
    }
}
```

`move_number` contains the total number of moves played so far, by both players. This means
this number will be even if the current player is white, and odd if the current player is black.

`hand` is a list with two elements, the hands of white and black. Each element is a dictionary
mapping tile types to the number of tiles of that type the player still possesses.
The tile types are as listed for queen bee, beetle, soldier ant, spider and grasshopper
respectively.

`board` is a dictionary mapping coordinates in the cubic coordinate system used in both this
repository and the starter code repository to lists of tiles. Each list contains the stack of
tiles at the given position, with the top-most tile being the last element of the list. Each tile
is itself a list of two elements, the player number, 0 for white and 1 for black, and the single
character abbreviation of the tile type.

The response is the JSON-encoded move the AI suggests in the given position. This can be either a
play, a move or a pass. The formats for each are as follows.

```
["play", "B", "0,0"]
["move", "0,0", "0,1"]
["pass", null, null]
```

A play indicates the tile to play and the position to play it at, and a move first indicates the
position the tile is currently at, and then the position the tile should be moved to. A pass has
no parameters.

The application end point is licensed under the MIT license, see `LICENSE.md`. The AI
implementation is licensed for educational use, see `vidstige/LICENSE.md`. Questions
and comments can be directed to [Ralf van den Broek](https://github.com/ralfvandenbroek).