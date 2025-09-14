CREATE TABLE IF NOT EXISTS brands (
  id SERIAL PRIMARY KEY,
  name TEXT UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS regions (
  id SERIAL PRIMARY KEY,
  name TEXT UNIQUE NOT NULL,
  yandex_geo_id BIGINT
);

CREATE TABLE IF NOT EXISTS phrases (
  id SERIAL PRIMARY KEY,
  template TEXT UNIQUE NOT NULL  -- пример: "[бренд] купить"
);

CREATE TABLE IF NOT EXISTS stats (
  id BIGSERIAL PRIMARY KEY,
  region_id INT NOT NULL REFERENCES regions(id),
  brand_id INT NOT NULL REFERENCES brands(id),
  phrase_id INT NOT NULL REFERENCES phrases(id),
  final_query TEXT NOT NULL,
  query_count INT NOT NULL,
  collected_at TIMESTAMP NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_stats_collected_at ON stats(collected_at);
CREATE UNIQUE INDEX IF NOT EXISTS idx_stats_uni
  ON stats(region_id, brand_id, phrase_id, date_trunc('day', collected_at));
