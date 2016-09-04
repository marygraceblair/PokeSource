CREATE TABLE IF NOT EXISTS types (
  `id` INTEGER unsigned NOT NULL PRIMARY KEY,
  `codename` VARCHAR(255),
  `generation_id` INTEGER unsigned,
  `damage_class_id` INTEGER unsigned
);

CREATE INDEX IF NOT EXISTS `idx_types_generation_id` ON `types` (`generation_id`);

CREATE INDEX IF NOT EXISTS `idx_types_damage_class_id` ON `types` (`damage_class_id`);