document.addEventListener('DOMContentLoaded', () => {

    const paymentDialog = document.getElementById('payment-confirm');

    if (paymentDialog) {

        const orderIdInput = document.getElementById('payment-confirm-order-id');

        const codeEl = document.getElementById('payment-confirm-code');

        const metaEl = document.getElementById('payment-confirm-meta');

        const cancelBtn = paymentDialog.querySelector('[data-payment-cancel]');

        let triggerButton = null;



        const restoreFocus = () => {

            if (triggerButton) {

                triggerButton.focus();

                triggerButton = null;

            }

        };



        const closeDialog = () => {

            if (!paymentDialog.open) {

                return;

            }

            paymentDialog.close();

        };



        const buildMetaLine = (button) => {

            const parts = [];

            const customer = button.dataset.customer || '';

            const total = button.dataset.total || '';

            const method = button.dataset.method || '';

            const ref = button.dataset.ref || '';



            if (customer !== '') {

                parts.push(`Customer: ${customer}`);

            }

            if (total !== '') {

                parts.push(total);

            }

            if (method !== '') {

                parts.push(method);

            }

            if (ref !== '') {

                parts.push(`Ref ${ref}`);

            }



            return parts.join(' · ');

        };



        document.querySelectorAll('[data-payment-open]').forEach((button) => {

            button.addEventListener('click', () => {

                triggerButton = button;



                if (orderIdInput) {

                    orderIdInput.value = button.dataset.orderId || '';

                }

                if (codeEl) {

                    codeEl.textContent = button.dataset.code || '';

                }

                if (metaEl) {

                    metaEl.textContent = buildMetaLine(button);

                }



                paymentDialog.showModal();

            });

        });



        cancelBtn?.addEventListener('click', closeDialog);



        paymentDialog.addEventListener('close', restoreFocus);



        paymentDialog.addEventListener('click', (event) => {

            const rect = paymentDialog.getBoundingClientRect();

            const clickedInDialog =

                rect.top <= event.clientY &&

                event.clientY <= rect.top + rect.height &&

                rect.left <= event.clientX &&

                event.clientX <= rect.left + rect.width;



            if (!clickedInDialog) {

                closeDialog();

            }

        });

    }



    const productFormDialog = document.getElementById('product-form-dialog');
    if (productFormDialog) {
        const productForm = document.getElementById('product-form');
        const productIdField = document.getElementById('product-id');
        const nameField = document.getElementById('product-name');
        const categoryField = document.getElementById('product-category');
        const descriptionField = document.getElementById('product-description');
        const priceField = document.getElementById('product-price');
        const stockField = document.getElementById('product-stock');
        const badgeField = document.getElementById('product-badge');
        const activeField = document.getElementById('product-active');
        const imageField = document.getElementById('product-image');
        const imagePathField = document.getElementById('product-image-path');
        const imagePathWrap = document.getElementById('product-image-path-wrap');
        const imageLabel = document.getElementById('product-image-label');
        const currentImageWrap = document.getElementById('product-current-image');
        const currentImageSrc = document.getElementById('product-current-image-src');
        const currentImagePath = document.getElementById('product-current-image-path');
        const formTitle = document.getElementById('product-form-title');
        const formKicker = document.getElementById('product-form-kicker');
        const formSubmit = document.getElementById('product-form-submit');
        const productsDataEl = document.getElementById('inventory-products-data');

        let productsById = {};
        if (productsDataEl) {
            try {
                productsById = JSON.parse(productsDataEl.textContent || '{}') || {};
            } catch (err) {
                productsById = {};
            }
        }

        let formTrigger = null;

        const setMode = (mode, product) => {
            const isEdit = mode === 'edit' && product;
            if (formTitle) {
                formTitle.textContent = isEdit ? 'Edit product' : 'Add product';
            }
            if (formKicker) {
                formKicker.textContent = isEdit ? 'Edit product' : 'New product';
            }
            if (formSubmit) {
                formSubmit.textContent = isEdit ? 'Save changes' : 'Create product';
            }
            if (imageLabel) {
                imageLabel.textContent = isEdit ? 'Product image (optional)' : 'Product image (required)';
            }
            if (imagePathWrap) {
                imagePathWrap.classList.toggle('hidden', Boolean(isEdit));
            }
            if (imagePathField) {
                imagePathField.disabled = Boolean(isEdit);
                if (isEdit) {
                    imagePathField.value = '';
                }
            }
        };

        const fillCreate = () => {
            productForm?.reset();
            if (productIdField) {
                productIdField.value = '0';
            }
            if (categoryField) {
                categoryField.value = 'fresh';
            }
            if (badgeField) {
                badgeField.value = '';
            }
            if (activeField) {
                activeField.checked = true;
            }
            if (priceField) {
                priceField.value = '';
            }
            if (stockField) {
                stockField.value = '0';
            }
            if (currentImageWrap) {
                currentImageWrap.classList.add('hidden');
                currentImageWrap.classList.remove('flex');
            }
            if (currentImageSrc) {
                currentImageSrc.removeAttribute('src');
            }
            if (currentImagePath) {
                currentImagePath.textContent = '';
            }
            setMode('new');
        };

        const fillEdit = (product) => {
            productForm?.reset();
            if (productIdField) {
                productIdField.value = String(product.id);
            }
            if (nameField) {
                nameField.value = product.name || '';
            }
            if (categoryField) {
                categoryField.value = product.category || 'fresh';
            }
            if (descriptionField) {
                descriptionField.value = product.description || '';
            }
            if (priceField) {
                priceField.value = product.price_php != null ? String(product.price_php) : '';
            }
            if (stockField) {
                stockField.value = product.stock != null ? String(product.stock) : '0';
            }
            if (badgeField) {
                badgeField.value = product.badge || '';
            }
            if (activeField) {
                activeField.checked = Number(product.is_active) === 1;
            }
            const hasImage = Boolean(product.image_path);
            if (currentImageWrap) {
                currentImageWrap.classList.toggle('hidden', !hasImage);
                currentImageWrap.classList.toggle('flex', hasImage);
            }
            if (currentImageSrc) {
                if (product.image_url) {
                    currentImageSrc.src = product.image_url;
                } else {
                    currentImageSrc.removeAttribute('src');
                }
            }
            if (currentImagePath) {
                currentImagePath.textContent = product.image_path || '';
            }
            setMode('edit', product);
        };

        const closeFormDialog = () => {
            if (productFormDialog.open) {
                productFormDialog.close();
            }
        };

        const clearFormQuery = () => {
            const url = new URL(window.location.href);
            if (!url.searchParams.has('new') && !url.searchParams.has('edit')) {
                return;
            }
            url.searchParams.delete('new');
            url.searchParams.delete('edit');
            url.searchParams.set('tab', 'inventory');
            window.history.replaceState({}, '', url.pathname + url.search);
        };

        document.querySelectorAll('[data-product-form-open]').forEach((button) => {
            button.addEventListener('click', () => {
                formTrigger = button;
                const productId = button.dataset.productId || '';
                if (productId !== '') {
                    const product = productsById[productId];
                    if (!product) {
                        return;
                    }
                    fillEdit(product);
                } else {
                    fillCreate();
                }
                productFormDialog.showModal();
                nameField?.focus();
            });
        });

        productFormDialog.querySelectorAll('[data-product-form-cancel]').forEach((button) => {
            button.addEventListener('click', closeFormDialog);
        });

        productFormDialog.addEventListener('close', () => {
            if (formTrigger) {
                formTrigger.focus();
                formTrigger = null;
            }
            clearFormQuery();
        });

        productFormDialog.addEventListener('click', (event) => {
            const rect = productFormDialog.getBoundingClientRect();
            const clickedInDialog =
                rect.top <= event.clientY &&
                event.clientY <= rect.top + rect.height &&
                rect.left <= event.clientX &&
                event.clientX <= rect.left + rect.width;
            if (!clickedInDialog) {
                closeFormDialog();
            }
        });

        const autoOpen = productFormDialog.dataset.autoOpen || '';
        if (autoOpen === 'new' || autoOpen === 'edit') {
            productFormDialog.showModal();
            nameField?.focus();
        }
    }

    const productDialog = document.getElementById('product-confirm');

    if (!productDialog) {

        return;

    }



    const productIdInput = document.getElementById('product-confirm-id');

    const productActiveInput = document.getElementById('product-confirm-active');

    const productMessageEl = document.getElementById('product-confirm-message');

    const productTitleEl = document.getElementById('product-confirm-title');

    const productSubmitBtn = document.getElementById('product-confirm-submit');

    const productCancelBtn = productDialog.querySelector('[data-product-cancel]');

    let productTriggerButton = null;



    const restoreProductFocus = () => {

        if (productTriggerButton) {

            productTriggerButton.focus();

            productTriggerButton = null;

        }

    };



    const closeProductDialog = () => {

        if (!productDialog.open) {

            return;

        }

        productDialog.close();

    };



    document.querySelectorAll('[data-product-open]').forEach((button) => {

        button.addEventListener('click', () => {

            productTriggerButton = button;



            const action = button.dataset.action || '';

            const productId = button.dataset.productId || '';

            const productName = button.dataset.productName || 'this product';



            if (productIdInput) {

                productIdInput.value = productId;

            }



            if (action === 'hide') {

                if (productTitleEl) {

                    productTitleEl.textContent = 'Hide product';

                }

                if (productMessageEl) {

                    productMessageEl.textContent = `Hide ${productName} from the shop?`;

                }

                if (productActiveInput) {

                    productActiveInput.value = '0';

                    productActiveInput.disabled = false;

                }

                if (productSubmitBtn) {

                    productSubmitBtn.name = 'set_product_active';

                    productSubmitBtn.value = '1';

                    productSubmitBtn.textContent = 'Hide product';

                    productSubmitBtn.className = 'px-4 py-2 bg-[#4c1719] text-white rounded text-[10px] font-bold uppercase hover:opacity-90';

                }

            } else if (action === 'show') {

                if (productTitleEl) {

                    productTitleEl.textContent = 'Show product';

                }

                if (productMessageEl) {

                    productMessageEl.textContent = `Show ${productName} in the shop again?`;

                }

                if (productActiveInput) {

                    productActiveInput.value = '1';

                    productActiveInput.disabled = false;

                }

                if (productSubmitBtn) {

                    productSubmitBtn.name = 'set_product_active';

                    productSubmitBtn.value = '1';

                    productSubmitBtn.textContent = 'Show product';

                    productSubmitBtn.className = 'px-4 py-2 bg-emerald-700 text-white rounded text-[10px] font-bold uppercase hover:bg-emerald-800';

                }

            } else if (action === 'delete') {

                if (productTitleEl) {

                    productTitleEl.textContent = 'Delete product';

                }

                if (productMessageEl) {

                    productMessageEl.textContent = `Permanently delete ${productName}? This cannot be undone.`;

                }

                if (productActiveInput) {

                    productActiveInput.value = '';

                    productActiveInput.disabled = true;

                }

                if (productSubmitBtn) {

                    productSubmitBtn.name = 'delete_product';

                    productSubmitBtn.value = '1';

                    productSubmitBtn.textContent = 'Delete permanently';

                    productSubmitBtn.className = 'px-4 py-2 bg-red-700 text-white rounded text-[10px] font-bold uppercase hover:bg-red-800';

                }

            }



            productDialog.showModal();

        });

    });



    productCancelBtn?.addEventListener('click', closeProductDialog);



    productDialog.addEventListener('close', restoreProductFocus);



    productDialog.addEventListener('click', (event) => {

        const rect = productDialog.getBoundingClientRect();

        const clickedInDialog =

            rect.top <= event.clientY &&

            event.clientY <= rect.top + rect.height &&

            rect.left <= event.clientX &&

            event.clientX <= rect.left + rect.width;



        if (!clickedInDialog) {

            closeProductDialog();

        }

    });

});

