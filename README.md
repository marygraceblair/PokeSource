# Poketto Monstaa - Data
Pokémon API with all data from the main RPG series RPG, also available in CSV, Protocol Buffer and SQLite DB formats.

## Requirements
The main requirement for compiling de `dist` folder is to have Docker installed in your machine,
otherwise you would have to install PHP, Python, Node and other required libraries by your own.

## Setup
After cloning or downloading the project, you need to install some prerequisites via:
```bash
./app setup
```

The process will take a while, since it recreates the veekun SQLite DB from the original veekun CSV files,
exports the Showdown data to JSON and runs the migration files to make the DB ready.

Afterwards you can start, optionally the API:
```bash
./app start
```

## The Project

### Versioning
The versioning or tagging of this project follows more or less the semver convention of 3 numbers, but
with the format `GENERATION.REMAKE.RELEASE`, which has obviously a different meaning:

- _`GENERATION`_: This version increases for each new generation that introduces important metagame changes.
- _`REMAKE`_: Generation remake or revision version. Usually remakes introduce slightly new changes and additions
to the current generation like new forms, new moves, items, etc.
- _`RELEASE`_: This is the version of the project itself. Breaking changes will increment this number by 100, while other
non breaking changes, new features and bug fixes will increment the number by 10 or 1 respectively.
For each new Generation or Revision versions this number will be reset to zero.

For example, for *Ultra Sun and Ultra Moon*, the tags will start with `7.1.0`.

### Data Source
The data source comes directly from the 
[`veekun/pokedex`](https://github.com/veekun/pokedex) CSV files,
which is, in the major part, generated from the ripped videogame real data.

In this fork, a set of changes called `migrations` will be applied on top of the original project
in order to fix, simplify, standardise, optimize and complement the original database.

Data differences with veekun/pokedex:

- Conquest, Pal Park and Pokeathlon data is omitted in the exports, but the tables will be still in the DB.
- Simplified tables are created for easier queries (tables starting with `zz_` like `zz_pokemon`)

This project alsom may use data from *Pokemon Showdown* like movesets, tiers, etc.

### Planned Changes
- Drop support for unofficial data (non official languages, abilities, etc.).
- Drop support for non core main-series data (Conquest, XD, Colosseum etc).
- Drop support for mini game data (PokeAthlon, Contests and Super Contests, Pal Park, etc).
- (Maybe) drop support for the locations and encounters data. It is only necessary to keep the information whether a Pokemon
is obtainable or not in a game, via event, etc.
- Import some data from Showdown (tiers, strategic movesets, etc).
- Simplify pokemon, pokemon_species, pokemon_forms, pokemon_types, pokemon_abilities and pokemon_egg_group
tables for better maintainability
- Add support for different Pokemon stats/moves/etc depending on the Generation and/or Version Group,
to keep track of the changes through all generations. Currently is not possible to know that.
- Simple web app to visualize the data

## API usage
The project comes with a builtin API that communicates directly with the database.
For learning how to use it, check the [PHP-CRUD-API](https://github.com/mevdschee/php-crud-api#usage) project.

For starting the API (if is not yet started), you should run:

```bash
./app start
```

Then you can access it navigating to [http://localhost:8151](http://localhost:8151).

## Maintenance

Run this command to see the list of available maintenance scripts:
```bash
./app help
```

Running all migrations against a copy of the `veekun/pokedex` SQLite DB file,
which would be inside the `bundle` directory:
```bash
./app migrate
```

Export the current state of the DB into the various formats:
```bash
./app dump
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
