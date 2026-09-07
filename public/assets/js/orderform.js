(function () {
    const root = document.getElementById('orderform-app');
    if (!root) {
        return;
    }

    const csrfToken = root.getAttribute('data-csrf') || '';
    const productId = root.getAttribute('data-product-id') || '';
    const pickupClasses =
        'py-3 px-4 rounded-sm border text-xs font-bold tracking-wider flex items-center justify-center gap-2 transition bg-kdesigns-burgundy text-white border-kdesigns-burgundy';
    const idleClasses =
        'py-3 px-4 rounded-sm border text-xs font-bold tracking-wider flex items-center justify-center gap-2 transition bg-white text-gray-800 border-kdesigns-inputBorder hover:bg-gray-50';

    window.setFulfillment = function setFulfillment(method) {
        const input = document.getElementById('fulfillmentInput');
        const pickupBtn = document.getElementById('pickupBtn');
        const deliveryBtn = document.getElementById('deliveryBtn');
        const summaryCard = document.getElementById('deliverySummaryCard');
        if (!input || !pickupBtn || !deliveryBtn || !summaryCard) {
            return;
        }

        input.value = method;

        if (method === 'pickup') {
            pickupBtn.className = pickupClasses;
            deliveryBtn.className = idleClasses;
            summaryCard.classList.add('hidden');
        }
    };

    window.openDeliveryModal = function openDeliveryModal() {
        const modal = document.getElementById('deliveryModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    };

    window.closeDeliveryModal = function closeDeliveryModal() {
        const modal = document.getElementById('deliveryModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    };

    window.saveDeliveryDetails = function saveDeliveryDetails(event) {
        event.preventDefault();

        const receiver = document.getElementById('modalReceiver')?.value || '';
        const contact = document.getElementById('modalContact')?.value || '';
        const location = document.getElementById('modalLocation')?.value || '';

        const fulfillmentInput = document.getElementById('fulfillmentInput');
        const pickupBtn = document.getElementById('pickupBtn');
        const deliveryBtn = document.getElementById('deliveryBtn');
        if (fulfillmentInput) {
            fulfillmentInput.value = 'delivery';
        }
        if (pickupBtn) {
            pickupBtn.className = idleClasses;
        }
        if (deliveryBtn) {
            deliveryBtn.className = pickupClasses;
        }

        const sumReceiver = document.getElementById('sumReceiver');
        const sumContact = document.getElementById('sumContact');
        const sumLocation = document.getElementById('sumLocation');
        if (sumReceiver) sumReceiver.innerText = receiver;
        if (sumContact) sumContact.innerText = contact;
        if (sumLocation) sumLocation.innerText = location;

        const summaryCard = document.getElementById('deliverySummaryCard');
        if (summaryCard) {
            summaryCard.classList.remove('hidden');
        }

        const formData = new FormData();
        formData.append('action', 'save_delivery');
        formData.append('receiver_name', receiver);
        formData.append('receiver_contact', contact);
        formData.append('delivery_location', location);
        formData.append('csrf_token', csrfToken);

        fetch('orderform.php?id=' + encodeURIComponent(productId), {
            method: 'POST',
            body: formData
        }).then(() => {
            window.closeDeliveryModal();
        });
    };
})();
