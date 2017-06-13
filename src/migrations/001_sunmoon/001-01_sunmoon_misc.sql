-- TODO: all translations

-- REGIONS

INSERT INTO regions (id, identifier)
VALUES
  (7, 'alola');

INSERT INTO region_names (region_id, local_language_id, name)
VALUES
  (7, 1, 'アローラ'), -- japanese
  (7, 2, 'Alora'), -- japanese romaji
  (7, 3, '알로라'), -- korean
  (7, 4, '阿羅拉'), -- chinese
  (7, 5, 'Alola'), -- french
  (7, 6, 'Alola'), -- german
  (7, 7, 'Alola'), -- spanish
  (7, 8, 'Alola'), -- italian
  (7, 9, 'Alola') -- english
;

-- VERSIONS

INSERT INTO version_groups (id, identifier, generation_id, `order`)
VALUES
  (17, 'sun-moon', 7, 17);

INSERT INTO version_group_regions (version_group_id, region_id)
VALUES
  (17, 7);

INSERT INTO versions (id, version_group_id, identifier)
VALUES
  (27, 17, 'sun'),
  (28, 17, 'moon');

INSERT INTO version_names (version_id, local_language_id, name)
VALUES
  (27, 9, 'Sun'),
  (28, 17, 'Moon');

--- MOVE METHODS

INSERT INTO pokemon_move_methods (id, identifier)
VALUES
  (11, 'evolution'),
  (12, 'zygarde-cube');

INSERT INTO pokemon_move_method_prose (pokemon_move_method_id, local_language_id, name, description)
VALUES
  (11, 9, 'Evolution', 'Can be taught once the Pokemon evolves.'),
  (12, 9, 'Zygarde Cube', 'Can be taught via the Zygarde Cube.');

INSERT INTO version_group_pokemon_move_methods (version_group_id, pokemon_move_method_id)
VALUES
  (17, 11),
  (17, 12);

--- POKEDEXES

INSERT INTO pokedexes (id, region_id, identifier, is_main_series)
VALUES
  (16, 7, 'melemele', 1),
  (17, 7, 'akala', 1),
  (18, 7, 'ulaula', 1),
  (19, 7, 'poni', 1);

INSERT INTO pokedex_prose (pokedex_id, local_language_id, name, description)
VALUES
  (16, 9, 'Melemele', null),
  (17, 9, 'Akala', null),
  (18, 9, 'Ula''ula', null),
  (19, 9, 'Poni', null);

INSERT INTO pokedex_version_groups (pokedex_id, version_group_id)
VALUES
  (16, 17),
  (17, 17),
  (18, 17),
  (19, 17);