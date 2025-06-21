function updateClock() {
    const now = new Date();
    document.getElementById("clock").textContent = now.toLocaleTimeString('es-ES');
}
setInterval(updateClock, 1000);
updateClock();

navigator.mediaDevices.getUserMedia({ video: true })
    .then(stream => { document.getElementById("video").srcObject = stream; })
    .catch(error => { console.error("Error al acceder a la cámara:", error); });

let userLocation = "";
let map = null;

function initMap(lat, lon) {
    if (map !== null) {
        map.remove(); 
    }
    document.getElementById("map").innerHTML = ""; 

    setTimeout(() => {
        map = L.map('map').setView([lat, lon], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        L.marker([lat, lon]).addTo(map)
            .bindPopup("Ubicación actual")
            .openPopup();

        setTimeout(() => { map.invalidateSize(); }, 500);
    }, 100);
}

if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(position => {
        userLocation = `${position.coords.latitude}, ${position.coords.longitude}`;
        initMap(position.coords.latitude, position.coords.longitude);
    }, () => {
        alert("No se pudo obtener la ubicación.");
    });
} else {
    alert("Geolocalización no soportada.");
}

document.getElementById("registerExitBtn").addEventListener("click", async function () {
    const canvas = document.createElement("canvas");
    const video = document.getElementById("video");

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const photoBase64 = canvas.toDataURL("image/png");

    const dateEnd = new Date().toISOString().slice(0, 19).replace("T", " ");

    console.log("📷 Foto Base64:", photoBase64.length > 100 ? "OK (imagen capturada)" : "❌ Error, sin imagen");
    console.log("📍 Ubicación:", userLocation ? userLocation : "❌ Error, ubicación vacía");

    if (!photoBase64 || !userLocation) {
        alert("Error: La foto o la ubicación no están disponibles.");
        return;
    }

    const response = await fetch("register_exit.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ photoBase64, userLocation, dateEnd })
    });

    const result = await response.json();
    console.log("📡 Respuesta del servidor:", result);
    
    if (result.success) {
        alert("✅ Salida registrada correctamente.");
        window.location.href = result.redirect; // Redirige al login
    } else {
        alert("❌ Error al registrar salida: " + result.message);
    }
});

    // Función para cancelar y redirigir al login
    function cancel() {
        window.location.href = "login_user.php"; // Redirige a la página de login
    }

