CREATE DATABASE IF NOT EXISTS `test_meteorology`;
USE test_meteorology;


DROP TABLE IF EXISTS weather;
DROP TABLE IF EXISTS cities;



CREATE TABLE `cities` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `country` varchar(50) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



CREATE TABLE `weather` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `city_id` bigint(20) unsigned NOT NULL,
    `timezone` varchar(50) NOT NULL,
    `latitud` varchar(50) NOT NULL,
    `longitud` varchar(50) NOT NULL,
    `forecast` varchar(50) NOT NULL,
    `description` varchar(50) NOT NULL,
    `icon` varchar(50) NOT NULL,
    `temp` varchar(50) NOT NULL,
    `feels_like` varchar(50) NOT NULL,
    `temp_min` varchar(50) NOT NULL,
    `temp_max` varchar(50) NOT NULL,
    `pressure` varchar(50) NOT NULL,
    `humidity` varchar(50) NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



ALTER TABLE weather ADD KEY fk_weather_cities (city_id);


ALTER TABLE weather ADD CONSTRAINT fk_weather_cities 
    FOREIGN KEY (city_id) 
    REFERENCES cities (id) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE;


INSERT INTO `cities` (`id`, `name`, `country`) VALUES 
(1, 'Miami', 'US'), 
(2, 'Orlando', 'US'),
(3, 'New York', 'US');


INSERT INTO `weather` (`id`, `city_id`, `timezone`, `latitud`, `longitud`, `forecast`, `description`, `icon`, `temp`, `feels_like`, `temp_min`, `temp_max`, `pressure`, `humidity`, `created_at`) VALUES
(1, 1, '-14400', '25.7743', '-80.1937', 'Clouds', 'muy nuboso', '04d', '30.59', '34.95', '28.88', '32.01', '1021', '64', '2021-06-09 12:23:50'),
(2, 2, '-14400', '28.5383', '-81.3792', 'Clouds', 'nubes', '04d', '32.1', '34.55', '29.26', '34.52', '1021', '50', '2021-06-09 12:23:50'),
(3, 3, '-14400', '40.7143', '-74.006', 'Clouds', 'nubes dispersas', '03d', '31.28', '33.97', '28.3', '33.48', '1016', '54', '2021-06-09 12:23:50'),
(4, 1, '-14400', '25.7743', '-80.1937', 'Clouds', 'muy nuboso', '04d', '30.77', '34.85', '28.88', '32.23', '1021', '62', '2021-06-09 20:43:37'),
(5, 2, '-14400', '28.5383', '-81.3792', 'Clouds', 'algo de nubes', '02d', '33.34', '35.3', '30.94', '35.39', '1020', '44', '2021-06-09 20:43:37'),
(6, 3, '-14400', '40.7143', '-74.006', 'Rain', 'lluvia ligera', '10d', '31.89', '34.9', '28.3', '34.65', '1015', '53', '2021-06-09 20:43:37');
