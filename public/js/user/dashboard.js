
function updateClock() {
    const now = new Date();
    document.getElementById("clock").textContent = now.toLocaleTimeString('es-ES');
}
setInterval(updateClock, 1000);
updateClock();

// Acceder a la cámara
navigator.mediaDevices.getUserMedia({ video: true })
    .then(stream => { document.getElementById("video").srcObject = stream; })
    .catch(error => { console.error("Error al acceder a la cámara:", error); });

// Variables globales para la ubicación
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

document.getElementById("registerBtn").addEventListener("click", async function () {
    const canvas = document.createElement("canvas");
    const video = document.getElementById("video");

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const photoBase64 = canvas.toDataURL("image/png");

    const dateStart = new Date().toISOString().slice(0, 19).replace("T", " ");
    const typeLogin = document.querySelector('input[name="type_login"]:checked').value;


    console.log("📷 Foto Base64:", photoBase64.length > 100 ? "OK (imagen capturada)" : "❌ Error, sin imagen");
    console.log("📍 Ubicación:", userLocation ? userLocation : "❌ Error, ubicación vacía");
    console.log("🔘 Tipo de Registro:", typeLogin);

    if (!photoBase64 || !userLocation) {
        alert("Error: La foto o la ubicación no están disponibles.");
        return;
    }

    const response = await fetch("register_assistance.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ 
            photoBase64, 
            userLocation, 
            dateStart,
            typeLogin 
        })
    });

    const result = await response.json();
    console.log("📡 Respuesta del servidor:", result);
    
    if (result.success) {
        alert("Registro exitoso. Cerrando sesión.");
        window.location.href = "login_user.php";
    } else {
        alert("Error al registrar: " + result.message);
    }
});

function cancel() {
    window.location.href = "login_user.php"; // Redirige a la página de login
}


const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    const registerBtn = document.getElementById('registerBtn');

    // Desactivar botón al iniciar
    registerBtn.disabled = true;
    registerBtn.style.opacity = "0.6";
    registerBtn.style.cursor = "not-allowed";

    async function setupCamera() {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        video.onloadedmetadata = () => {
            video.play();
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
        };
    }

    async function startFaceDetection() {
        await setupCamera();
        const model = await blazeface.load();
    
        async function detect() {
            const predictions = await model.estimateFaces(video, false);
            ctx.clearRect(0, 0, canvas.width, canvas.height);
    
            if (predictions.length > 0) {
                let hasRequiredFeatures = true;
                
                predictions.forEach(pred => {
                    // Dibujar el rectángulo alrededor de la cara
                    const [x, y] = pred.topLeft;
                    const [x2, y2] = pred.bottomRight;
                    const width = x2 - x;
                    const height = y2 - y;
    
                    ctx.beginPath();
                    ctx.lineWidth = 3;
                    ctx.strokeStyle = "green";
                    ctx.rect(x, y, width, height);
                    ctx.stroke();
    
                    // Verificar puntos faciales
                    if (!pred.landmarks) {
                        hasRequiredFeatures = false;
                        return;
                    }
    
                    // Los landmarks siguen este orden:
                    // 0: ojo derecho, 1: ojo izquierdo, 2: nariz, 3: boca (centro), 4: oreja derecha, 5: oreja izquierda
                    const requiredLandmarks = pred.landmarks.slice(0, 4); // Solo necesitamos ojos, nariz y boca
                    
                    // Dibujar y verificar cada landmark
                    requiredLandmarks.forEach((landmark, index) => {
                        const [lx, ly] = landmark;
                        
                        // Dibujar puntos de referencia
                        ctx.beginPath();
                        ctx.arc(lx, ly, 5, 0, 2 * Math.PI);
                        ctx.fillStyle = index === 3 ? 'red' : 'blue'; // Boca en rojo, otros en azul
                        ctx.fill();
                        
                        // Verificar si el punto está dentro de los límites del video
                        if (lx < 0 || lx > canvas.width || ly < 0 || ly > canvas.height) {
                            hasRequiredFeatures = false;
                        }
                    });
                });
    
                // Activar/desactivar botón según detección
                registerBtn.disabled = !hasRequiredFeatures;
                registerBtn.style.opacity = hasRequiredFeatures ? "1" : "0.6";
                registerBtn.style.cursor = hasRequiredFeatures ? "pointer" : "not-allowed";
                
            } else {
                // Si no hay rostro, desactiva el botón
                registerBtn.disabled = true;
                registerBtn.style.opacity = "0.6";
                registerBtn.style.cursor = "not-allowed";
            }
    
            requestAnimationFrame(detect);
        }
    
        detect();
    }

    window.addEventListener("load", startFaceDetection);