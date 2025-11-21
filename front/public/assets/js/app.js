const API_BASE_URL = "/api-proxy.php";
const CART_KEY = "awesome_ride_cart";

function getCart() {
    try {
        const raw = localStorage.getItem(CART_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch (e) {
        console.error("Erreur lecture panier", e);
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
}

function formatPrice(value) {
    return new Intl.NumberFormat("fr-FR", {
        style: "currency",
        currency: "EUR",
    }).format(value);
}

async function fetchJson(url, options = {}) {
    const opts = {
        method: "GET",
        ...options,
    };

    const method = (opts.method || "GET").toUpperCase();

    const headers = {
        Accept: "application/json",
        ...(opts.headers || {}),
    };

    if (method !== "GET" && !headers["Content-Type"]) {
        headers["Content-Type"] = "application/json";
    }

    opts.headers = headers;

    const res = await fetch(url, opts);

    if (!res.ok) {
        throw new Error(`Erreur API ${res.status}`);
    }

    return res.json();
}

async function initHomePage() {
    const grid = document.getElementById("products-grid");
    const status = document.getElementById("catalogue-status");
    if (!grid) return;

    status.textContent = "Chargement du catalogue…";
    grid.setAttribute("aria-busy", "true");

    try {
        const products = await fetchJson(`${API_BASE_URL}?endpoint=products`);

        grid.innerHTML = "";
        grid.setAttribute("aria-busy", "false");

        if (!products.length) {
            grid.innerHTML = "<p>Aucun produit disponible pour le moment.</p>";
            status.textContent = "Aucun produit disponible.";
            return;
        }

        products.forEach((p) => {
            const card = document.createElement("article");
            card.className = "product-card";

            const imgSrc = p.image || "/assets/img/placeholder-product.jpg";

            card.innerHTML = `
                <img src="${imgSrc}" alt="${p.nom}">
                <h3 class="product-name">${p.nom}</h3>
                <p class="product-price">${formatPrice(p.prix)}</p>
                <p class="product-desc">${p.description ?? ""}</p>
                <div class="product-actions">
                    <a href="/product.php?id=${p.id}" class="btn">Voir le produit</a>
                    <button type="button" class="btn btn-primary" data-add-to-cart="${p.id}">
                        Ajouter au panier
                    </button>
                </div>
            `;
            grid.appendChild(card);
        });

        grid.addEventListener("click", (event) => {
            const btn = event.target.closest("[data-add-to-cart]");
            if (!btn) return;
            const productId = parseInt(btn.getAttribute("data-add-to-cart"), 10);
            const product = products.find((p) => p.id === productId);
            if (product) {
                addProductToCart({
                    id: product.id,
                    nom: product.nom,
                    prix: product.prix,
                    options: null,
                });
            }
        });
    } catch (e) {
        console.error(e);
        grid.innerHTML = "<p>Impossible de charger les produits pour le moment.</p>";
        grid.setAttribute("aria-busy", "false");
    }
}

function addProductToCart(item) {
    const cart = getCart();
    const key = item.id + (item.options ? "-" + JSON.stringify(item.options) : "");
    const existing = cart.find((line) => line.key === key);

    if (existing) {
        existing.quantite += 1;
    } else {
        cart.push({
            key,
            id: item.id,
            nom: item.nom,
            prix: item.prix,
            quantite: 1,
            options: item.options || null,
        });
    }

    saveCart(cart);
    alert("Produit ajouté au panier.");
}
function formatOptionsForDisplay(options) {
    if (!options) return "-";

    const labels = {
        cap: "Capuchon",
        body: "Corps",
        mine: "Mine",
    };

    const parts = [];
    for (const [key, value] of Object.entries(options)) {
        const label = labels[key] || key;
        // On met la valeur avec première lettre en maj
        const capitalized =
            typeof value === "string"
                ? value.charAt(0).toUpperCase() + value.slice(1)
                : value;
        parts.push(`${label} : ${capitalized}`);
    }

    return parts.join(", ");
}

function initCartPage() {
    const cartEmpty = document.getElementById("cart-empty");
    const cartContent = document.getElementById("cart-content");
    const tbody = document.getElementById("cart-items");
    const totalEl = document.getElementById("cart-total");
    const status = document.getElementById("cart-status");

    if (!tbody) return;

    const cart = getCart();
    if (!cart.length) {
        if (cartEmpty) cartEmpty.hidden = false;
        if (cartContent) cartContent.hidden = true;
        if (status) status.textContent = "Panier vide.";
        return;
    }

    if (cartEmpty) cartEmpty.hidden = true;
    if (cartContent) cartContent.hidden = false;

    let total = 0;
    tbody.innerHTML = "";

    cart.forEach((line, index) => {
        const lineTotal = line.prix * line.quantite;
        total += lineTotal;

        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${line.nom}</td>
            <td>${formatOptionsForDisplay(line.options)}</td>
            <td>
                <input type="number" min="1" value="${line.quantite}" data-cart-index="${index}">
            </td>
            <td>${formatPrice(line.prix)}</td>
            <td>${formatPrice(lineTotal)}</td>
            <td>
                <button type="button" class="btn" data-remove-index="${index}">
                    Supprimer
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    if (totalEl) {
        totalEl.textContent = formatPrice(total);
    }
    if (status) {
        status.textContent = "Panier chargé.";
    }

    tbody.addEventListener("input", (event) => {
        const input = event.target.closest("input[data-cart-index]");
        if (!input) return;
        const index = parseInt(input.getAttribute("data-cart-index"), 10);
        const newQty = parseInt(input.value, 10);
        if (isNaN(newQty) || newQty < 1) return;

        const cart = getCart();
        if (!cart[index]) return;
        cart[index].quantite = newQty;
        saveCart(cart);
        initCartPage(); // on re-render
    });

    tbody.addEventListener("click", (event) => {
        const btn = event.target.closest("button[data-remove-index]");
        if (!btn) return;
        const index = parseInt(btn.getAttribute("data-remove-index"), 10);
        const cart = getCart();
        cart.splice(index, 1);
        saveCart(cart);
        initCartPage();
    });
}

async function initProductPage() {
    const container = document.getElementById("product-details");
    if (!container) return;

    const productId = parseInt(container.getAttribute("data-product-id"), 10);
    if (!productId) {
        container.innerHTML = "<p>Produit introuvable.</p>";
        container.setAttribute("aria-busy", "false");
        return;
    }

    container.setAttribute("aria-busy", "true");

    try {
        const product = await fetchJson(`${API_BASE_URL}?endpoint=products&id=${productId}`);
        container.setAttribute("aria-busy", "false");

        const isCustomPen =
            product.is_customizable == 1 ||
            product.is_customizable === true ||
            product.is_customizable === "1";

        container.innerHTML = `
            <div class="product-details-inner">
                <div class="product-image">
                    <img src="${product.image || "/assets/img/placeholder-product.jpg"}" alt="${product.nom}">
                </div>
                <div class="product-info">
                    <h1>${product.nom}</h1>
                    <p class="product-price">${formatPrice(product.prix)}</p>
                    <p>${product.description ?? ""}</p>
                    
${isCustomPen ? `
    <form id="pen-config-form" class="pen-config-form">
        <h2>Personnalisez votre stylo</h2>

        <div class="form-group">
            <label for="pen-cap">Capuchon</label>
            <select id="pen-cap" name="cap"></select>
        </div>

        <div class="form-group">
            <label for="pen-body">Corps</label>
            <select id="pen-body" name="body"></select>
        </div>

        <div class="form-group">
            <label for="pen-mine">Mine</label>
            <select id="pen-mine" name="mine"></select>
        </div>

        <button type="submit" class="btn btn-primary">
            Ajouter au panier
        </button>
    </form>
` : `
    <button id="btn-add-product" class="btn btn-primary">
        Ajouter au panier
    </button>
`}

                </div>
            </div>
        `;

        if (isCustomPen) {
            const form = document.getElementById("pen-config-form");
            const config = product.custom_config
                ? JSON.parse(product.custom_config)
                : null;

            function populateSelect(selectEl, values) {
                selectEl.innerHTML = "";
                if (!values || !values.length) {
                    const opt = document.createElement("option");
                    opt.value = "";
                    opt.textContent = "—";
                    selectEl.appendChild(opt);
                    return;
                }
                values.forEach((val) => {
                    const opt = document.createElement("option");
                    opt.value = val;
                    opt.textContent = val.charAt(0).toUpperCase() + val.slice(1);
                    selectEl.appendChild(opt);
                });
            }

            populateSelect(
                document.getElementById("pen-cap"),
                config && Array.isArray(config.cap) ? config.cap : ["bleu", "noir", "rouge"]
            );

            populateSelect(
                document.getElementById("pen-body"),
                config && Array.isArray(config.body) ? config.body : ["blanc", "noir"]
            );

            populateSelect(
                document.getElementById("pen-mine"),
                config && Array.isArray(config.mine) ? config.mine : ["fine", "medium"]
            );

            form.addEventListener("submit", (event) => {
                event.preventDefault();
                const options = {
                    cap: form.cap.value,
                    body: form.body.value,
                    mine: form.mine.value,
                };
                addProductToCart({
                    id: product.id,
                    nom: product.nom + " (personnalisé)",
                    prix: product.prix,
                    options,
                });
            });
        } else {
            const btn = document.getElementById("btn-add-product");
            btn.addEventListener("click", () => {
                addProductToCart({
                    id: product.id,
                    nom: product.nom,
                    prix: product.prix,
                    options: null,
                });
            });
        }
    } catch (e) {
        console.error(e);
        container.setAttribute("aria-busy", "false");
        container.innerHTML = "<p>Erreur lors du chargement du produit.</p>";
    }
}

function initCheckoutPage() {
    const form = document.getElementById("checkout-form");
    const messages = document.getElementById("checkout-messages");
    if (!form) return;

    const cart = getCart();
    if (!cart.length) {
        messages.innerHTML = `<p class="text-muted">Votre panier est vide. Retournez à la boutique.</p>`;
        form.hidden = true;
        return;
    }

    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        messages.innerHTML = "";

        const data = {
            nom: form.nom.value.trim(),
            prenom: form.prenom.value.trim(),
            email: form.email.value.trim(),
            adresse: form.adresse.value.trim(),
            items: getCart(),
        };

        if (!data.nom || !data.prenom || !data.email || !data.adresse) {
            messages.innerHTML = `<p class="text-muted">Merci de remplir tous les champs obligatoires.</p>`;
            return;
        }

        try {
            await fetchJson(`${API_BASE_URL}?endpoint=orders`, {
                method: "POST",
                body: JSON.stringify(data),
            });

            localStorage.removeItem(CART_KEY);
            messages.innerHTML = `<p>Votre commande a bien été enregistrée.</p>`;
            form.reset();
        } catch (e) {
            console.error(e);
            messages.innerHTML = `<p>Erreur lors de l’enregistrement de la commande. Merci de réessayer plus tard.</p>`;
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    const page = document.body.getAttribute("data-page");

    switch (page) {
        case "home":
            initHomePage();
            break;
        case "product":
            initProductPage();
            break;
        case "cart":
            initCartPage();
            break;
        case "checkout":
            initCheckoutPage();
            break;
    }
});
