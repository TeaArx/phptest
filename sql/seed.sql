INSERT INTO brands (name) VALUES
  ('Chery'), ('Tank'), ('Omoda'), ('Haval')
ON CONFLICT DO NOTHING;

INSERT INTO regions (name, yandex_geo_id) VALUES
  ('Архангельск', NULL),
  ('Северодвинск', NULL),
  ('Вологда', NULL),
  ('Череповец', NULL)
ON CONFLICT DO NOTHING;

INSERT INTO phrases (template) VALUES
  ('[бренд] купить'),
  ('[бренд] где купить'),
  ('[бренд] цена')
ON CONFLICT DO NOTHING;
