CREATE TABLE IF NOT EXISTS zz_pokemon AS SELECT
  p3.id as id, p1.id as pokemon_species_id, p2.id as pokemon_id, 1 as revision,
  p1.identifier as name,

  COALESCE(
      CASE WHEN (p1.identifier = p2.identifier AND p1.identifier != p3.identifier) THEN p3.identifier ELSE NULL END,
      CASE WHEN (p1.identifier != p2.identifier AND p1.identifier = p3.identifier) THEN p2.identifier ELSE NULL END,
      CASE WHEN (p1.identifier != p2.identifier) THEN p2.identifier ELSE NULL END,
      NULL
  ) as form_name,

  (CASE WHEN (form_identifier = '') THEN NULL ELSE form_identifier END) as form_name_suffix,


  type_1, type_2, ability_1, ability_2, ability_hidden, egg_group_n1 as egg_group_1,

  (CASE WHEN (egg_group_n2 != egg_group_n1) THEN egg_group_n2 ELSE null END) as egg_group_2,

  height, weight,
  base_hp, yield_hp, base_attack, yield_attack, base_defense, yield_defense,
  base_sp_attack, yield_sp_attack, base_sp_defense, yield_sp_defense, base_speed, yield_speed,
  (base_hp + base_attack + base_defense + base_sp_attack + base_sp_defense + base_speed) as base_stats_total,
  (yield_hp + yield_attack + yield_defense + yield_sp_attack + yield_sp_defense + yield_speed) as yield_stats_total,

  generation_id, introduced_in_version_group_id as introduced_in, evolves_from_species_id, evolution_chain_id,
  color_id as color, shape_id as shape, gender_rate, capture_rate as catch_rate, base_happiness, base_experience,
  hatch_counter as egg_cycles, has_gender_differences as has_gender_traits, growth_rate_id as growth_rate,

  COALESCE(
      CASE WHEN (p1.id != p2.id AND is_mega = 1) THEN 3 ELSE NULL END, -- mega
      CASE WHEN (p1.id != p2.id AND form_identifier = 'primal') THEN 4 ELSE NULL END, -- primal
      CASE WHEN (p1.id != p2.id AND is_mega != 1) THEN 1 ELSE NULL END, -- regular
      CASE WHEN (p1.id = p2.id AND p1.id != p3.id) THEN 2 ELSE NULL END, -- special or aesthetic
      NULL
  ) as form_category,

  (CASE WHEN (forms_switchable = 1) THEN 0 ELSE 1 END) as is_permanent_form,
  is_battle_only as is_battle_form,
  (CASE WHEN  (p1.id = p2.id AND p1.id != p3.id) THEN 1 ELSE 0 END)
    as is_aesthetic_form, -- this may not be accurate (e.g. burmy, arceus, furfrou and many others are false positives)

  p2.is_default as is_default, p3.is_default as form_is_default,
  p1."order" as species_order, p2."order" as pokemon_order, form_order, p3."order" as form_order2
FROM pokemon_species as p1
  LEFT JOIN pokemon as p2 ON p1.id=p2.species_id
  LEFT JOIN pokemon_forms as p3 ON p2.id=p3.pokemon_id
  LEFT JOIN (SELECT pokemon_id, base_stat as base_hp, effort as yield_hp
             FROM pokemon_stats WHERE stat_id = 1) AS st1 ON st1.pokemon_id = p2.id
  LEFT JOIN (SELECT pokemon_id, base_stat as base_attack, effort as yield_attack
             FROM pokemon_stats WHERE stat_id = 2) AS st2 ON st2.pokemon_id = p2.id
  LEFT JOIN (SELECT pokemon_id, base_stat as base_defense, effort as yield_defense
             FROM pokemon_stats WHERE stat_id = 3) AS st3 ON st3.pokemon_id = p2.id
  LEFT JOIN (SELECT pokemon_id, base_stat as base_sp_attack, effort as yield_sp_attack
             FROM pokemon_stats WHERE stat_id = 4) AS st4 ON st4.pokemon_id = p2.id
  LEFT JOIN (SELECT pokemon_id, base_stat as base_sp_defense, effort as yield_sp_defense
             FROM pokemon_stats WHERE stat_id = 5) AS st5 ON st5.pokemon_id = p2.id
  LEFT JOIN (SELECT pokemon_id, base_stat as base_speed, effort as yield_speed
             FROM pokemon_stats WHERE stat_id = 6) AS st6 ON st6.pokemon_id = p2.id
  LEFT JOIN (SELECT pokemon_id, type_id as type_1
             FROM pokemon_types WHERE slot = 1) AS ty1 ON ty1.pokemon_id = p2.id
  LEFT JOIN (SELECT pokemon_id, type_id as type_2
             FROM pokemon_types WHERE slot = 2) AS ty2 ON ty2.pokemon_id = p2.id
  LEFT JOIN (SELECT pokemon_id, ability_id as ability_1
             FROM pokemon_abilities WHERE slot = 1) AS ab1 ON ab1.pokemon_id = p2.id
  LEFT JOIN (SELECT pokemon_id, ability_id as ability_2
             FROM pokemon_abilities WHERE slot = 2) AS ab2 ON ab2.pokemon_id = p2.id
  LEFT JOIN (SELECT pokemon_id, ability_id as ability_hidden
             FROM pokemon_abilities WHERE slot = 3) AS abh ON abh.pokemon_id = p2.id
  LEFT JOIN (SELECT species_id, MIN(egg_group_id) as egg_group_n1
             FROM pokemon_egg_groups GROUP BY species_id) AS egg1 ON egg1.species_id = p1.id
  LEFT JOIN (SELECT species_id, MAX(egg_group_id) as egg_group_n2
             FROM pokemon_egg_groups GROUP BY species_id) AS egg2 ON egg2.species_id = p1.id
-- WHERE p1.id = p2.id AND p2.id = p3.id -- only species
-- WHERE p1.id = p3.id OR p1.id != p2.id -- only species and non-cosmetic forms
-- WHERE p1.id != p2.id -- only non-cosmetic forms
-- WHERE p1.id != p2.id OR p1.id != p3.id -- only non-cosmetic and cosmetic forms
-- WHERE p1.id = p2.id AND p1.id != p3.id -- only cosmetic forms
ORDER BY species_order, pokemon_order, form_order, form_order2;