<?php
$pageTitle = "Détail commande – Back-office";
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/models/OrderModel.php';

Auth::requireAdmin();

$orderModel = new OrderModel();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: /admin/orders.php');
    exit;
}

$errorMessage = null;
$successMessage = null;

// Liste des statuts possibles
$allowedStatuses = [
    'nouvelle'       => 'Nouvelle',
    'en_preparation' => 'En préparation',
    'expediee'       => 'Expédiée',
    'annulee'        => 'Annulée',
];

// Traitement du formulaire de changement de statut
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['statut'] ?? '';

    if (!array_key_exists($newStatus, $allowedStatuses)) {
        $errorMessage = "Statut invalide.";
    } else {
        if ($orderModel->updateStatus($id, $newStatus)) {
            $successMessage = "Le statut de la commande a été mis à jour.";
        } else {
            $errorMessage = "Impossible de mettre à jour le statut de la commande.";
        }
    }
}

// On recharge la commande après éventuelle mise à jour
$order = $orderModel->getOrderById($id);
if (!$order) {
    header('Location: /admin/orders.php');
    exit;
}

$items = $orderModel->getOrderItems($id);

// Formatage date : dd/mm/yyyy hh:mm:ss
$formattedDate = $order['date'];
try {
    $dt = new DateTime($order['date']);
    $formattedDate = $dt->format('d/m/Y H:i:s');
} catch (Throwable $e) {
    // Si ça plante, on garde la valeur brute
}

require __DIR__ . '/../partials/admin-header.php';
?>

<h1>Commande #<?= (int)$order['id'] ?></h1>
<p class="admin-subtitle">
    Détail de la commande, des articles et gestion du statut.
</p>

<section class="card">
    <?php if ($errorMessage): ?>
        <p style="color:#b91c1c;"><?= htmlspecialchars($errorMessage) ?></p>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <p style="color:#166534;"><?= htmlspecialchars($successMessage) ?></p>
    <?php endif; ?>

    <p>
        <strong>Date :</strong> <?= htmlspecialchars($formattedDate) ?><br>
        <strong>Client :</strong> <?= htmlspecialchars($order['prenom'] . ' ' . $order['nom']) ?><br>
        <strong>Email :</strong> <?= htmlspecialchars($order['email']) ?><br>
        <strong>Total :</strong> <?= number_format($order['total'], 2, ',', ' ') ?> €
    </p>

    <form method="post" style="margin-top: .75rem; margin-bottom: 1rem;">
        <label for="statut"><strong>Statut de la commande :</strong></label>
        <select id="statut" name="statut">
            <?php foreach ($allowedStatuses as $value => $label): ?>
                <option value="<?= htmlspecialchars($value) ?>"
                    <?= $order['statut'] === $value ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-primary" style="margin-left: .5rem;">
            Mettre à jour
        </button>
    </form>

    <h2>Articles</h2>

    <?php if (!$items): ?>
        <p>Aucun article pour cette commande.</p>
    <?php else: ?>
        <table class="cart-table">
            <thead>
            <tr>
                <th>Produit</th>
                <th>Personnalisation</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $line): ?>
                <?php
                $optionsText = '-';

                if (!empty($line['options'])) {
                    $opts = json_decode($line['options'], true);

                    if (is_array($opts) && $opts) {
                        $parts = [];
                        foreach ($opts as $key => $value) {
                            switch ($key) {
                                case 'cap':
                                    $label = 'Capuchon';
                                    break;
                                case 'body':
                                    $label = 'Corps';
                                    break;
                                case 'mine':
                                    $label = 'Mine';
                                    break;
                                default:
                                    $label = ucfirst($key);
                            }
                            $parts[] = $label . ' : ' . $value;
                        }
                        $optionsText = implode(', ', $parts);
                    }
                }
                ?>
                <tr>
                    <td><?= htmlspecialchars($line['product_name']) ?></td>
                    <td><?= htmlspecialchars($optionsText) ?></td>
                    <td><?= (int)$line['quantite'] ?></td>
                    <td><?= number_format($line['prix_achat'], 2, ',', ' ') ?> €</td>
                    <td><?= number_format($line['prix_achat'] * $line['quantite'], 2, ',', ' ') ?> €</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>


    <p style="margin-top: 1rem;">
        <a class="btn" href="/admin/orders.php">Retour à la liste des commandes</a>
    </p>
</section>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
