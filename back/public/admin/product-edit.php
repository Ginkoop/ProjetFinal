<?php
$pageTitle = "Édition produit – Back-office";
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/models/ProductModel.php';
require_once __DIR__ . '/../../src/models/CategoryModel.php';

Auth::requireAdmin();

$productModel  = new ProductModel();
$categoryModel = new CategoryModel();

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode = $id > 0 ? 'edit' : 'create';
$product = null;

if ($mode === 'edit') {
    $product = $productModel->getProductById($id);
    if (!$product) {
        header('Location: /admin/products.php');
        exit;
    }
}

$categories = $categoryModel->getAllCategories();
$error   = null;
$success = null;
// Décodage de la configuration de personnalisation existante
$customConfig = [
        'cap'  => [],
        'body' => [],
        'mine' => [],
];

if (!empty($product['custom_config'])) {
    $decoded = json_decode($product['custom_config'], true);
    if (is_array($decoded)) {
        $customConfig['cap']  = $decoded['cap']  ?? [];
        $customConfig['body'] = $decoded['body'] ?? [];
        $customConfig['mine'] = $decoded['mine'] ?? [];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom         = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix        = (float)($_POST['prix'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 0);
    $image       = trim($_POST['image'] ?? '');
    $categoryId  = (int)($_POST['category_id'] ?? 0);
    $isCustomizable = isset($_POST['is_customizable']) ? 1 : 0;
// Valeurs possibles pour la personnalisation (séparées par des virgules)
    $capValues  = trim($_POST['cap_values'] ?? '');
    $bodyValues = trim($_POST['body_values'] ?? '');
    $mineValues = trim($_POST['mine_values'] ?? '');

// On convertit "bleu,noir,rouge" en ['bleu','noir','rouge']
    $configArray = [
            'cap'  => $capValues !== ''  ? array_map('trim', explode(',', $capValues))  : [],
            'body' => $bodyValues !== '' ? array_map('trim', explode(',', $bodyValues)) : [],
            'mine' => $mineValues !== '' ? array_map('trim', explode(',', $mineValues)) : [],
    ];

// Si rien n'est renseigné, on met null pour ne pas stocker un JSON vide
    $customConfigJson = (!empty($configArray['cap']) || !empty($configArray['body']) || !empty($configArray['mine']))
            ? json_encode($configArray, JSON_UNESCAPED_UNICODE)
            : null;

    if ($nom === '' || $prix <= 0 || $categoryId <= 0) {
        $error = "Merci de renseigner au minimum le nom, un prix et une catégorie.";
    } else {
        // On prépare le tableau de données attendu par ProductModel
        $data = [
                'nom'             => $nom,
                'description'     => $description !== '' ? $description : null,
                'prix'            => $prix,
                'stock'           => $stock,
                'image'           => $image !== '' ? $image : null,
                'category_id'     => $categoryId,
                'is_customizable' => $isCustomizable,
                'custom_config'   => $customConfigJson,
        ];

        if ($mode === 'create') {
            $newId = $productModel->createProduct($data);
            $success = "Produit créé avec succès (ID $newId).";
            $product = $productModel->getProductById($newId);
            $mode = 'edit';
            $id   = $newId;
        } else {
            $productModel->updateProduct($id, $data);
            $success = "Produit mis à jour.";
            $product = $productModel->getProductById($id);
        }
    }
}

require __DIR__ . '/../partials/admin-header.php';
?>

<h1><?= $mode === 'create' ? "Ajouter un produit" : "Modifier le produit" ?></h1>
<p class="admin-subtitle">
    Définissez les informations du produit (nom, prix, stock, catégorie).
</p>

<section class="card">
    <?php if ($error): ?>
        <p style="color:#fecaca;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color:#bbf7d0;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post" novalidate class="checkout-form">
        <div class="form-group">
            <label for="nom">Nom</label>
            <input id="nom" name="nom" type="text" required
                   value="<?= htmlspecialchars($product['nom'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="prix">Prix (€)</label>
            <input id="prix" name="prix" type="number" step="0.01" min="0"
                   value="<?= htmlspecialchars($product['prix'] ?? '0') ?>">
        </div>

        <div class="form-group">
            <label for="stock">Stock</label>
            <input id="stock" name="stock" type="number" min="0"
                   value="<?= htmlspecialchars($product['stock'] ?? '0') ?>">
        </div>

        <div class="form-group">
            <label for="image">Image (chemin ou URL)</label>
            <input id="image" name="image" type="text"
                   value="<?= htmlspecialchars($product['image'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="category_id">Catégorie</label>
            <select id="category_id" name="category_id" required>
                <option value="">-- Choisir une catégorie --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>"
                            <?= isset($product['category_id']) && (int)$product['category_id'] === (int)$cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- ✅ Nouveau : produit personnalisable -->
        <div class="form-group">
            <label>
                <input
                        type="checkbox"
                        name="is_customizable"
                        value="1"
                        <?= !empty($product['is_customizable']) ? 'checked' : '' ?>
                >
                Produit personnalisable (stylo)
            </label>
            <p class="field-help">
                Cochez cette case pour activer le configurateur de personnalisation sur le front (capuchon, corps, mine).
            </p>
        </div>
        <!-- Configuration des options de personnalisation -->
        <div class="form-group">
            <label for="cap_values">Couleurs de capuchon (séparées par des virgules)</label>
            <input
                    id="cap_values"
                    name="cap_values"
                    type="text"
                    placeholder="ex : bleu, noir, rouge"
                    value="<?= htmlspecialchars(implode(', ', $customConfig['cap'])) ?>"
            >
        </div>

        <div class="form-group">
            <label for="body_values">Couleurs de corps (séparées par des virgules)</label>
            <input
                    id="body_values"
                    name="body_values"
                    type="text"
                    placeholder="ex : blanc, noir"
                    value="<?= htmlspecialchars(implode(', ', $customConfig['body'])) ?>"
            >
        </div>

        <div class="form-group">
            <label for="mine_values">Types de mine (séparées par des virgules)</label>
            <input
                    id="mine_values"
                    name="mine_values"
                    type="text"
                    placeholder="ex : fine, medium"
                    value="<?= htmlspecialchars(implode(', ', $customConfig['mine'])) ?>"
            >
        </div>

        <button type="submit" class="btn btn-primary">
            <?= $mode === 'create' ? "Créer le produit" : "Enregistrer les modifications" ?>
        </button>
    </form>

</section>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
