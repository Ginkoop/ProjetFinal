INSERT INTO users (nom, prenom, email, password, role) VALUES
('Admin', 'Principal', 'admin@laboiteaobjets.test', 'admin123', 'admin'),
('Martin', 'Julie', 'julie.martin@test.fr', 'password', 'client'),
('Dupont', 'Marc', 'marc.dupont@test.fr', 'password', 'client'),
('Durand', 'Clara', 'clara.durand@test.fr', 'password', 'client');

INSERT INTO categories (nom) VALUES
('Mugs'),
('T-shirts'),
('Tote bags'),
('Stylos personnalisables');


INSERT INTO products (nom, description, prix, stock, image, category_id) VALUES
('Mug blanc 30cl',         'Mug en céramique blanc 30cl',              9.90,  50, 'mug-blanc-30cl.jpg',       1),
('Mug noir 30cl',          'Mug en céramique noir 30cl',              10.90, 40, 'mug-noir-30cl.jpg',        1),
('T-shirt coton blanc',    'T-shirt 100% coton blanc',                19.90, 30, 'tshirt-coton-blanc.jpg',   2),
('T-shirt oversize noir',  'T-shirt oversize noir unisexe',           24.90, 20, 'tshirt-oversize-noir.jpg', 2),
('Tote bag naturel',       'Tote bag en coton naturel',                7.90, 80, 'totebag-naturel.jpg',      3),
('Tote bag noir',          'Tote bag noir résistant',                  8.90, 60, 'totebag-noir.jpg',         3),
('Stylo personnalisable A','Stylo personnalisable modèle A',           4.90, 200,'stylo-pers-model-a.jpg',   4),
('Stylo personnalisable B','Stylo personnalisable modèle B',           5.50, 150,'stylo-pers-model-b.jpg',   4);

INSERT INTO orders (date, total, statut, user_id) VALUES
('2025-11-01 10:15:00', 24.70, 'nouvelle',       2),
('2025-11-02 15:42:00', 64.70, 'en_preparation', 3),
('2025-11-03 09:30:00', 34.70, 'expediee',       4);

-- Commande 1 (id = 1) : 2 x Mug blanc + 1 x Stylo A
INSERT INTO order_items (order_id, product_id, quantite, prix_achat) VALUES
(1, 1, 2, 9.90),
(1, 7, 1, 4.90);

-- Commande 2 (id = 2) : 1 x T-shirt oversize + 2 x T-shirt coton
INSERT INTO order_items (order_id, product_id, quantite, prix_achat) VALUES
(2, 4, 1, 24.90),
(2, 3, 2, 19.90);

-- Commande 3 (id = 3) : 3 x Tote bag naturel + 2 x Stylo B
INSERT INTO order_items (order_id, product_id, quantite, prix_achat) VALUES
(3, 5, 3, 7.90),
(3, 8, 2, 5.50);