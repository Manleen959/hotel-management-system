let map;
let markers = [];

// 🔥 Premium Toast UI
function showToast(message, type = "success") {
    const toast = document.createElement("div");
    toast.innerHTML = `<i class="fa ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;

    toast.style.position = "fixed";
    toast.style.bottom = "30px";
    toast.style.right = "30px";
    toast.style.background = type === "success" ? "#22c55e" : "#ef4444";
    toast.style.color = "white";
    toast.style.padding = "16px 24px";
    toast.style.borderRadius = "12px";
    toast.style.boxShadow = "0 10px 25px rgba(0,0,0,0.3)";
    toast.style.zIndex = "9999";
    toast.style.fontSize = "1rem";
    toast.style.fontWeight = "600";
    toast.style.display = "flex";
    toast.style.alignItems = "center";
    toast.style.gap = "10px";
    toast.style.transition = "all 0.4s ease";
    toast.style.transform = "translateY(100px)";

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.transform = "translateY(0)";
    }, 100);

    setTimeout(() => {
        toast.style.transform = "translateY(100px)";
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

// 🖼️ Open Gallery Modal
window.openGallery = function(imageUrl, name) {
    const modal = document.getElementById("galleryModal");
    const modalImg = document.getElementById("modalImg");
    
    if (!imageUrl) {
        showToast("No image available for this hotel", "error");
        return;
    }

    modalImg.src = imageUrl;
    modal.classList.add("active");
};

// 🔄 Load Hotels (MAP + CARDS)
async function loadHotels() {
    try {
        const res = await fetch("api/get_hotels.php");
        const hotels = await res.json();

        const list = document.getElementById("hotel-list");

        // Initialize markers array and define icon mapping
        markers.forEach(m => map.removeLayer(m));
        markers = [];
        list.innerHTML = "";

        const bounds = [];
        
        const getIcon = (amenity) => {
            const map = {
                'wifi': 'fa-wifi',
                'pool': 'fa-swimming-pool',
                'spa': 'fa-spa',
                'gym': 'fa-dumbbell',
                'breakfast': 'fa-coffee'
            };
            let a = amenity.toLowerCase().trim();
            return map[a] || 'fa-check';
        };

        hotels.forEach(hotel => {
            const lat = parseFloat(hotel.latitude);
            const lng = parseFloat(hotel.longitude);
            const imageUrl = hotel.image_url;
            const category = hotel.category || 'Hotel';
            const city = hotel.city || 'Unknown';
            const amenities = hotel.amenities ? hotel.amenities.split(',') : [];

            // 📍 Marker with Custom Popup
            const marker = L.marker([lat, lng])
                .addTo(map)
                .bindPopup(`
                    <div style="padding: 5px; min-width: 200px;">
                        <img src="${imageUrl}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-bottom: 8px;">
                        <b style="font-size: 1.1rem; display: block; margin-bottom: 4px;">${hotel.name}</b>
                        <span style="color: var(--text-muted); font-size: 0.85rem;"><i class="fa fa-map-marker-alt"></i> ${city}</span><br>
                        <span style="color: #22d3ee; font-weight: 700; font-size: 1.1rem; display: block; margin-top: 5px;">₹${hotel.price}</span>
                    </div>
                `);

            markers.push(marker);
            bounds.push([lat, lng]);
            
            let badgesHTML = amenities.map(a => `<span class="badge"><i class="fa ${getIcon(a)}"></i> ${a.trim()}</span>`).join('');

            // 🧾 Card UI
            list.innerHTML += `
                <div class="card" 
                     data-name="${hotel.name.toLowerCase()}" 
                     data-city="${city.toLowerCase()}" 
                     data-category="${category}" 
                     data-price="${hotel.price}">
                    <div style="background-image: url('${imageUrl}'); height: 200px; background-size: cover; background-position: center; border-radius: 12px; margin-bottom: 0.5rem; cursor: pointer; position: relative;" onclick="openGallery('${imageUrl}', '${hotel.name}')">
                        <span style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">${category}</span>
                    </div>
                    <h3>${hotel.name} <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 400; float: right;"><i class="fa fa-map-marker-alt"></i> ${city}</span></h3>
                    <div class="rating">
                        <i class="fa fa-star"></i>
                        <span>${hotel.rating}</span>
                    </div>
                    <div class="amenities-badges">
                        ${badgesHTML}
                    </div>
                    <p class="price" style="margin-top: 10px;">₹${hotel.price} <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 400;">/ night</span></p>
                    <div class="card-actions" style="margin-top: 15px;">
                        <button class="btn-book" onclick="bookHotel(${hotel.id})" style="flex: 1;">Book Now</button>
                    </div>
                </div>
            `;
        });

        // Fit bounds
        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    } catch (err) {
        console.error("Failed to load hotels:", err);
    }
}

// 📍 Zoom
window.zoomToHotel = function (lat, lng) {
    map.setView([lat, lng], 15);
};

// 📅 Booking
window.bookHotel = function (id) {
    if(!confirm("Proceed with booking?")) return;

    fetch("api/book.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `hotel_id=${id}`
    })
    .then(res => res.text())
    .then(data => {
        showToast(data, data.toLowerCase().includes("failed") ? "error" : "success");
    })
    .catch(err => {
        console.error(err);
        showToast("Booking failed. Please try again.", "error");
    });
};

let currentCategory = 'All';
let minPrice = 0;
let maxPrice = 99999;

const applyFilters = () => {
    const value = document.getElementById("search").value.toLowerCase();
    const cards = document.querySelectorAll(".card");

    cards.forEach(card => {
        const name = card.getAttribute("data-name");
        const city = card.getAttribute("data-city");
        const category = card.getAttribute("data-category");
        const price = parseInt(card.getAttribute("data-price"));

        const matchSearch = name.includes(value) || city.includes(value);
        const matchCategory = currentCategory === 'All' || category === currentCategory;
        const matchPrice = price >= minPrice && price <= maxPrice;

        card.style.display = (matchSearch && matchCategory && matchPrice) ? "flex" : "none";
    });
};

// 🔍 Search
window.searchHotel = applyFilters;

// 🎛️ Filter Category
window.filterCategory = function(cat, btn) {
    currentCategory = cat;
    document.querySelectorAll('.filter-group:nth-of-type(1) .filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyFilters();
}

// 💰 Filter Price
window.filterPrice = function(min, max, btn) {
    minPrice = min;
    maxPrice = max;
    document.querySelectorAll('.filter-group:nth-of-type(2) .filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyFilters();
}

// 🗺️ INIT MAP
async function initMap() {
    // Dark mode tile layer for premium feel
    map = L.map('map', {
        zoomControl: false // Hide default zoom for cleaner UI
    }).setView([22.9734, 78.6569], 5);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);
    
    // Add zoom control at bottom right
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    await loadHotels();

    // 📍 User location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const userMarker = L.circleMarker([pos.coords.latitude, pos.coords.longitude], {
                radius: 8,
                fillColor: "#22d3ee",
                color: "#fff",
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            }).addTo(map).bindPopup("📍 You are here");
        });
    }
}

// Load
window.onload = initMap;