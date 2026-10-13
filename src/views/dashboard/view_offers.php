<?php
require_once __DIR__ . '/../../controllers/OfferController.php';

$offerController = new OfferController();
$offers = $offerController->getOffers();
$message = $_SESSION['offer_message'] ?? '';
unset($_SESSION['offer_message']);
$pageTitle = 'Quarter Offers';
include __DIR__ . '/../layouts/header.php';
?>
<main class="max-w-4xl mx-auto w-full px-4 py-10">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between gap-4 border-b border-gray-200 pb-4 mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Quarter Offers</h1>
                <p class="text-sm text-gray-500 mt-1">Review and respond to your quarter offers</p>
            </div>
            <a href="<?= htmlspecialchars(baseUrl('dashboard'), ENT_QUOTES, 'UTF-8'); ?>" class="text-sm font-semibold text-[#5c060d]">Back to dashboard</a>
        </div>
        <?php if ($message): ?>
            <p class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <?php if ($offers): ?>
            <div class="divide-y divide-gray-200">
                <?php foreach ($offers as $offer): ?>
                    <section class="py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h2 class="font-semibold text-gray-900">Offer #<?= (int)$offer['id']; ?></h2>
                            <p class="text-sm text-gray-600 mt-1">Status: <?= htmlspecialchars($offer['status'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="text-xs text-gray-500 mt-1">Updated <?= htmlspecialchars($offer['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <?php if (in_array($offer['status'], ['approved', 'pending'], true)): ?>
                            <form method="post" action="<?= htmlspecialchars(baseUrl('offer/respond'), ENT_QUOTES, 'UTF-8'); ?>" class="flex flex-wrap gap-2">
                                <input type="hidden" name="offer_id" value="<?= (int)$offer['id']; ?>">
                                <button name="response" value="accept" class="rounded-md bg-emerald-700 px-3 py-2 text-sm font-semibold text-white">Accept</button>
                                <button name="response" value="postpone" class="rounded-md bg-amber-600 px-3 py-2 text-sm font-semibold text-white">Postpone</button>
                                <button name="response" value="deny" class="rounded-md bg-red-700 px-3 py-2 text-sm font-semibold text-white">Decline</button>
                            </form>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-sm text-gray-600">There are no quarter offers for your account.</p>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
