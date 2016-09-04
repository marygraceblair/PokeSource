CREATE TABLE IF NOT EXISTS location_areas (
  `id` INTEGER unsigned NOT NULL PRIMARY KEY,
  `location_id` INTEGER unsigned,
  `game_index` TEXT,
  `codename` VARCHAR(255)
);

CREATE INDEX IF NOT EXISTS `idx_location_areas_location_id` ON `location_areas` (`location_id`);