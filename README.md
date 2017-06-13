# pokedex-data
All data from main series Pokémon games, originally from the veekun/pokedex project, dumped as CSV files and SQLite DB.

The data source comes directly from the 
[`veekun/pokedex`](https://github.com/veekun/pokedex) CSV files,
which are mostly generated from the ripped videogame data (not 100% though).

In this fork, a set of changes called `migrations` will be applied on top of the original project
in order to fix, simplify, standardise, optimize and complement the original database (check the migrations roadmap).

## Requirements
- Docker

## Setup

To initialize the project you need to run this command:

```bash
./pokedex init
```

The process will take a while, since it recreates the veekun SQLite DB from the CSV files and exports the 
Showdown data to JSON.

## Maintenance

Run this command to see the list of available maintenance scripts:
```bash
./pokedex help
```

Running all migrations against the veekun pokedex SQLite DB
```bash
./pokedex migrate
```

Export the current state of the DB to CSV. This will save all the files under the `build/csv` folder.
```bash
./pokedex dump
```

## Migrations Roadmap

- ID columns for every table (single primary keys work better with ORMs).
- Robust CSV library (`csvkit`) to avoid CSV parsing errors and have more flexibility and options.
- Avoid human errors in CSV files by encouraging migration scripts over manual CSV changes.
- Drop support for unofficial data (non official languages, abilities, etc.).
- Drop support for non core main-series data (Conquest, Colosseum, XD, etc).
- Drop support for mini game data (PokeAthlon, Contests, etc).
- Add information from Showdown (tiers, strategies, etc).
- Add support for different Pokemon stats/moves/etc depending on the Generation and/or Version Group,
to keep track of the changes through all generations.
- Simplify pokemon, pokemon_species, pokemon_forms, pokemon_types, pokemon_abilities and pokemon_egg_group
tables for better maintainability

## License

This software is copyrighted and licensed under the 
[MIT license](https://github.com/metaunicorn/pokedex-data/LICENSE).

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
https://github.com/metaunicorn/pokedex-data
