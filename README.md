# Poketto Monstaa - Data
All data from Pokémon main series RPG games, available in CSV, Protocol Buffer and SQLite DB formats.

The data source comes directly from the 
[`veekun/pokedex`](https://github.com/veekun/pokedex) CSV files,
which is, in the major part, generated from the ripped videogame real data.

## Setup
After cloning or downloading the project, you need to start it via `docker-compose up` in the project folder.
Next step is the project initialization:

```bash
./pokedex init
```

The process will take a while, since it recreates the veekun SQLite DB from the original veekun CSV files
and exports the Showdown data to JSON.

## The Project

### Versioning
The versioning or tagging of this project follows more or less the semver convention of `MAJOR.MINOR.PATCH` but
with the format `GGR.MAJOR.MINORPATCH`, which has obviously a different meaning:

- _GGR_: This version increases for each new set of games that introduce important metagame changes.
    - _GG_: Is the part reserved to the generation identifier. It should have from 1 to 2 digits.
    - _R_:  Identifies the generation revision (remake) version. It should have only one digit.
    For example the version `70.0.0` would refer to `Sun` and `Moon`,
    where `71.0.0` would refer to `Ultra Sun` and `Utra Moon`.
- _MAJOR_: Project major version. Breaking changes that may alter the data structure, but not related to new games.
- _MINORPATCH_: Project minor and patch version: Bug fixes and any changes that are backwards compatible with the
generated data.

### Differences with veekun/pokedex
In this fork, a set of changes called `migrations` will be applied on top of the original project
in order to fix, simplify, standardise, optimize and complement the original database.

- Conquest, Pal Park and Pokeathlon data is omitted in the exports, but the tables will be still in the DB.
- Simplified tables are created for easier queries (tables starting with `zz_` like `zz_pokemon`)

### Roadmap and ideas
- Drop support for unofficial data (non official languages, abilities, etc.).
- Drop support for non core main-series data (Conquest, XD, Colosseum etc).
- Drop support for mini game data (PokeAthlon, Contests and Super Contests, Pal Park, etc).
- Add information from Showdown (tiers, strategies, etc).
- Simplify pokemon, pokemon_species, pokemon_forms, pokemon_types, pokemon_abilities and pokemon_egg_group
tables for better maintainability
- Add support for different Pokemon stats/moves/etc depending on the Generation and/or Version Group,
to keep track of the changes through all generations. Currently is not possible to know that.

## Requirements
The main requirement for compiling de `dist` folder is to have Docker installed in your machine,
otherwise you would have to install PHP, Python, Node and other required libraries by your own.

## Maintenance

Run this command to see the list of available maintenance scripts:
```bash
./pokedex help
```

Running all migrations against the `veekun/pokedex` SQLite DB file.
```bash
./pokedex migrate
```

Export the current state of the DB into the various formats:
```bash
./pokedex dump
```
This will save all the files under the `dist` folder, using various tools like `csvkit`, `twig`, etc.

## License

This software is copyrighted and licensed under the 
[MIT license](https://github.com/pokettomonstaa/data/LICENSE).

### Disclaimer

This software comes bundled with data and graphics extracted from the
Pokémon series of video games. Some terminology from the Pokémon franchise is
also necessarily used within the software itself. This is all the intellectual
property of Nintendo, Creatures, inc., and GAME FREAK, inc. and is protected by
various copyrights and trademarks.

The author believes that the use of this intellectual property for a fan reference
is covered by fair use and that the software is significantly impaired without said
property included. Any use of this copyrighted property is at your own legal risk.

This software is not affiliated in any way with Nintendo,
Pokémon or any other game company.

A complete revision history of this software is available from
https://github.com/pokettomonstaa/data
