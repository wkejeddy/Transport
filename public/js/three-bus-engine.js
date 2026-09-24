/**
 * Real Voyage GTA 5-Style "4D" 100% Photorealistic Bus Simulation Engine
 * Masterfully crafted to replicate the exact modern Cameroon luxury touring coach (Marcopolo New G7 / Scania High-Decker)
 * and its exact VIP passenger cabin based on user interior reference photograph.
 *
 * Features:
 * - 100% Photorealistic Cabin Seats matching the user's interior reference photo:
 *   - Deep slate charcoal-navy leather upholstery (#273849 / #1E293B) with realistic leather grain and specular sheen.
 *   - 3 distinct horizontal quilted cushion segments (lumbar pillow, mid-back pillow, and contoured headrest).
 *   - Ergonomic lateral side wings/bolsters that cradle the passenger.
 *   - Modern two-tone armrests (clean white uprights with dark padded leather arm caps) between all seats and on aisles.
 *   - Twin polished chrome pedestal floor legs.
 * - Ultra-Visible Cabin Architecture:
 *   - Light grey transit vinyl aisle flooring (#CBD5E1) with dual illuminated white safety edge runner strips.
 *   - Overhead luggage parcel racks with inset reading/AC pods.
 *   - Overhead interior downlight spots directly illuminating every row of seats for razor-sharp clarity.
 *   - Crystal-clear cutaway mode where roof is completely transparent/ghosted, giving 100% unobstructed visibility.
 *   - Default high-angle interior perspective framing the seats identically to the user's uploaded photograph.
 * - Interactive Seat Selection:
 *   - Generous raycast hitboxes, hover glow, and live 3D floating HUD tooltip.
 *   - Selected seats lift slightly (+0.18 units) and transition into vibrant luminous emerald green leather (#10B981).
 *   - Full two-way synchronization with Laravel booking form and passenger list.
 * - Authentic Exterior Coach Details:
 *   - Swept-back projector headlights with crystal DRL eyebrow, chrome radiator grille, Marcopolo B-pillar aero wave arch.
 *   - Dual cyan/sky-blue waistline stripes, lower red pinstripe, luggage bay latches, engine cooling louvers.
 *   - Deep-dish mirror-polished chrome tri-axle wheels (1 front + 2 rear twin axles) with grooved rubber tires.
 *   - Both Curbside Entrance Doors (Front & Middle) with 3-step illuminated staircases and synchronized 4D pneumatic animation.
 *   - Automotive showroom infinity stage with soft contact ambient occlusion ground shadow.
 */

class RealVoyageBus3D {
    constructor(config) {
        this.container = document.getElementById(config.containerId);
        this.seatsData = config.seats || [];
        this.totalSeats = config.totalSeats || 75;
        this.basePrice = config.basePrice || 5000;
        this.onSeatToggle = config.onSeatToggle || function() {};
        this.selectedSeats = new Set(config.initialSelected || []);

        // 4D Interactive States
        this.isRoofTransparent = true;
        this.activePerspective = 'interior'; // Default to crystal-clear cabin view matching reference photo
        this.timeOfDay = 'day'; // 'day', 'sunset', 'night'
        this.isDoorOpen = false;
        this.activeFirstPersonSeat = null;
        this.locale = config.locale || (document.documentElement.lang && document.documentElement.lang.startsWith('en') ? 'en' : 'fr');

        this.seatMeshes = {};
        this.seatMaterials = {};
        this.lights = {};
        this.headlightCones = [];
        this.doorGroup = null; // compatibility
        this.doors = { front: null, middle: null };

        // Three.js Core Objects
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.controls = null;
        this.raycaster = new THREE.Raycaster();
        this.mouse = new THREE.Vector2(-100, -100);
        this.coachGroup = new THREE.Group();
        this.roofMeshes = [];
        this.interactiveSeatObjects = [];
        this.envMap = null;

        // Texture Cache for Butter-Smooth Performance
        this.textureCache = {};

        this.init();
    }

    init() {
        if (!this.container) return;

        const width = this.container.clientWidth || 800;
        const height = Math.min(Math.max(window.innerHeight * 0.65, 520), 660);

        // 1. Scene Setup
        this.scene = new THREE.Scene();

        // 2. Camera Setup: Tuned to high-angle interior perspective matching the reference photo
        this.camera = new THREE.PerspectiveCamera(38, width / height, 0.1, 1000);
        this.camera.position.set(0, 8.4, 4.2);

        // 3. WebGL Renderer with ACESFilmic Tone Mapping for AAA Game Realism
        this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'high-performance' });
        this.renderer.setSize(width, height);
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.toneMappingExposure = 1.18;
        this.renderer.outputEncoding = THREE.sRGBEncoding;
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        this.container.appendChild(this.renderer.domElement);

        // 4. Smooth OrbitControls
        this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
        this.controls.enableDamping = true;
        this.controls.dampingFactor = 0.05;
        this.controls.maxPolarAngle = Math.PI / 2 + 0.01;
        this.controls.minDistance = 2.5;
        this.controls.maxDistance = 65;
        this.controls.target.set(0, 1.8, 8.8); // Focus on central seating deck
        this.controls.update();

        // 5. Procedural Studio Environment Reflection Map
        this.setupEnvironmentMap();

        // 6. Automotive Studio Lighting & 4D Dynamic Engine (Enhanced for vibrant seat visibility)
        this.setupDynamicLighting();

        // 7. Showroom Floor & Contact Ambient Occlusion Shadow
        this.setupStudioShowroomGround();

        // 8. Build 100% Photorealistic Cameroon Luxury Coach (Marcopolo New G7 Tri-Axle)
        this.buildPhotorealisticCoach();

        // 9. Interaction Events
        this.setupEvents();

        // 10. Start Animation Loop
        this.animate = this.animate.bind(this);
        requestAnimationFrame(this.animate);
    }

    /**
     * Procedural HDRI-Style Environment Map for Car Lacquer, Leather & Chrome Reflections
     */
    setupEnvironmentMap() {
        const pmremGenerator = new THREE.PMREMGenerator(this.renderer);
        pmremGenerator.compileEquirectangularShader();

        const canvas = document.createElement('canvas');
        canvas.width = 512;
        canvas.height = 256;
        const ctx = canvas.getContext('2d');

        // Studio Softbox Reflection Gradient
        const grad = ctx.createLinearGradient(0, 0, 0, 256);
        grad.addColorStop(0, '#FFFFFF');    // Overhead Softbox
        grad.addColorStop(0.2, '#E2E8F0');  // Sky Fill
        grad.addColorStop(0.48, '#FFFFFF'); // Horizon Rim Highlight
        grad.addColorStop(0.52, '#94A3B8'); // Ground Horizon
        grad.addColorStop(0.8, '#334155');  // Studio Floor
        grad.addColorStop(1, '#0F172A');    // Deep Chassis Shadow
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, 512, 256);

        // Studio Light Strip Accents
        ctx.fillStyle = 'rgba(255, 255, 255, 0.85)';
        ctx.fillRect(80, 40, 120, 25);
        ctx.fillRect(320, 40, 120, 25);

        const envTexture = new THREE.CanvasTexture(canvas);
        const renderTarget = pmremGenerator.fromEquirectangular(envTexture);
        this.envMap = renderTarget.texture;
        this.scene.environment = this.envMap;
    }

    /**
     * Dynamic Lighting System (Daylight Studio / Sunset / Midnight with Crisp Seat Downlights)
     */
    setupDynamicLighting() {
        // Ambient Fill
        this.lights.ambient = new THREE.AmbientLight(0xffffff, 0.85);
        this.scene.add(this.lights.ambient);

        // Key Studio Light (Front Three-Quarter)
        this.lights.sun = new THREE.DirectionalLight(0xffffff, 1.25);
        this.lights.sun.position.set(22, 30, 22);
        this.lights.sun.castShadow = true;
        this.lights.sun.shadow.mapSize.width = 2048;
        this.lights.sun.shadow.mapSize.height = 2048;
        this.lights.sun.shadow.camera.near = 0.5;
        this.lights.sun.shadow.camera.far = 100;
        const d = 26;
        this.lights.sun.shadow.camera.left = -d;
        this.lights.sun.shadow.camera.right = d;
        this.lights.sun.shadow.camera.top = d;
        this.lights.sun.shadow.camera.bottom = -d;
        this.lights.sun.shadow.bias = -0.0005;
        this.scene.add(this.lights.sun);

        // Soft Back/Rim Light (Gives chairs and body crisp edge highlights)
        this.lights.rim = new THREE.DirectionalLight(0xbae6fd, 0.7);
        this.lights.rim.position.set(-20, 20, -20);
        this.scene.add(this.lights.rim);

        // Overhead Bank of Downlight Spots Directly Illuminating the Cabin Seats
        const downlight1 = new THREE.PointLight(0xffffff, 1.1, 16);
        downlight1.position.set(0, 4.2, 8);
        this.coachGroup.add(downlight1);

        const downlight2 = new THREE.PointLight(0xffffff, 1.1, 16);
        downlight2.position.set(0, 4.2, 0);
        this.coachGroup.add(downlight2);

        const downlight3 = new THREE.PointLight(0xffffff, 1.1, 16);
        downlight3.position.set(0, 4.2, -8);
        this.coachGroup.add(downlight3);

        // Subtle Cyan Floor Track Illumination
        this.lights.neonAisle = new THREE.PointLight(0x38bdf8, 0.45, 14);
        this.lights.neonAisle.position.set(0, 1.7, 0);
        this.coachGroup.add(this.lights.neonAisle);

        this.setTimeOfDay('day');
    }

    setTimeOfDay(mode) {
        this.timeOfDay = mode;

        if (mode === 'day') {
            this.scene.background = new THREE.Color(0xf8fafc); // Clean automotive studio backdrop
            this.lights.ambient.intensity = 0.9;
            this.lights.ambient.color.setHex(0xffffff);
            this.lights.sun.intensity = 1.35;
            this.lights.sun.color.setHex(0xffffff);
            this.lights.sun.position.set(22, 30, 22);
            this.renderer.toneMappingExposure = 1.18;
            this.setHeadlightsActive(false);
        } else if (mode === 'sunset') {
            this.scene.background = new THREE.Color(0x451a03); // Golden Hour Studio
            this.lights.ambient.intensity = 0.6;
            this.lights.ambient.color.setHex(0xfed7aa);
            this.lights.sun.intensity = 1.45;
            this.lights.sun.color.setHex(0xf97316);
            this.lights.sun.position.set(30, 9, 15);
            this.renderer.toneMappingExposure = 1.12;
            this.setHeadlightsActive(true, 0.8);
        } else if (mode === 'night') {
            this.scene.background = new THREE.Color(0x020617); // Deep Midnight
            this.lights.ambient.intensity = 0.28;
            this.lights.ambient.color.setHex(0x1e293b);
            this.lights.sun.intensity = 0.18;
            this.lights.sun.color.setHex(0x38bdf8);
            this.renderer.toneMappingExposure = 1.35;
            this.setHeadlightsActive(true, 2.0);
        }
    }

    setHeadlightsActive(active, intensity = 1.8) {
        this.headlightCones.forEach(spot => {
            spot.intensity = active ? intensity : 0;
        });
    }

    /**
     * Automotive Studio Showroom Stage with Soft Ground Contact Shadow
     */
    setupStudioShowroomGround() {
        const groundGroup = new THREE.Group();

        // 1. Studio Floor Plane
        const floorGeo = new THREE.PlaneGeometry(160, 160);
        const floorMat = new THREE.MeshStandardMaterial({
            color: 0xf8fafc,
            roughness: 0.25,
            metalness: 0.1,
            envMap: this.envMap,
        });
        const floor = new THREE.Mesh(floorGeo, floorMat);
        floor.rotation.x = -Math.PI / 2;
        floor.position.y = 0;
        floor.receiveShadow = true;
        groundGroup.add(floor);

        // 2. Soft Ambient Occlusion Contact Shadow Map
        const shadowCanvas = document.createElement('canvas');
        shadowCanvas.width = 1024;
        shadowCanvas.height = 512;
        const sCtx = shadowCanvas.getContext('2d');

        const shadowGrad = sCtx.createRadialGradient(512, 256, 40, 512, 256, 440);
        shadowGrad.addColorStop(0, 'rgba(15, 23, 42, 0.75)');
        shadowGrad.addColorStop(0.35, 'rgba(15, 23, 42, 0.45)');
        shadowGrad.addColorStop(0.7, 'rgba(15, 23, 42, 0.15)');
        shadowGrad.addColorStop(1, 'rgba(15, 23, 42, 0)');

        sCtx.fillStyle = shadowGrad;
        sCtx.beginPath();
        sCtx.ellipse(512, 256, 460, 180, 0, 0, Math.PI * 2);
        sCtx.fill();

        const shadowTexture = new THREE.CanvasTexture(shadowCanvas);
        const shadowPlaneGeo = new THREE.PlaneGeometry(32, 10);
        const shadowPlaneMat = new THREE.MeshBasicMaterial({
            map: shadowTexture,
            transparent: true,
            opacity: 0.85,
            depthWrite: false,
        });
        const contactShadow = new THREE.Mesh(shadowPlaneGeo, shadowPlaneMat);
        contactShadow.rotation.x = -Math.PI / 2;
        contactShadow.position.set(0, 0.015, 0);
        groundGroup.add(contactShadow);

        this.scene.add(groundGroup);
    }

    /**
     * High-Resolution Procedural Texture Generators
     */

    // 1. Procedural High-Res Quilted Leather Texture for Backrest (3 Horizontal Segments from Photo)
    generateSeatBackrestTexture(seatNum, state = 'available') {
        const key = `backrest_${state}_${seatNum}`;
        if (this.textureCache[key]) return this.textureCache[key];

        const canvas = document.createElement('canvas');
        canvas.width = 256;
        canvas.height = 512;
        const ctx = canvas.getContext('2d');

        // Base Leather Palette matching photo
        let baseColor = '#253545'; // Deep slate charcoal-navy leather
        let highlightColor = '#3b4e61';
        let shadowColor = '#15212c';
        let stitchColor = '#101a23';

        if (state === 'selected') {
            baseColor = '#059669'; // Vibrant emerald green
            highlightColor = '#10B981';
            shadowColor = '#047857';
            stitchColor = '#064E3B';
        } else if (state === 'crew') {
            baseColor = '#0F2942'; // Luxury navy & gold
            highlightColor = '#1E3A8A';
            shadowColor = '#0A192F';
            stitchColor = '#D97706';
        } else if (state === 'booked') {
            baseColor = '#475569'; // Muted booked grey
            highlightColor = '#64748B';
            shadowColor = '#334155';
            stitchColor = '#1E293B';
        }

        // Leather Background
        ctx.fillStyle = baseColor;
        ctx.fillRect(0, 0, 256, 512);

        // Subtle leather grain texture
        for (let i = 0; i < 3500; i++) {
            ctx.fillStyle = Math.random() > 0.5 ? 'rgba(255,255,255,0.03)' : 'rgba(0,0,0,0.05)';
            ctx.fillRect(Math.random() * 256, Math.random() * 512, 2, 2);
        }

        // 3 Horizontal Quilted Cushion Segments (Identical to user's photo!)
        const drawHorizontalCrease = (y) => {
            // Shadow Crease
            ctx.fillStyle = shadowColor;
            ctx.fillRect(10, y, 236, 6);

            // Stitched Thread Line
            ctx.strokeStyle = stitchColor;
            ctx.lineWidth = 2;
            ctx.setLineDash([4, 3]);
            ctx.beginPath();
            ctx.moveTo(15, y + 2);
            ctx.lineTo(241, y + 2);
            ctx.stroke();
            ctx.setLineDash([]); // reset

            // Highlight Bevel (Gives deep 3D pillowing effect)
            ctx.fillStyle = highlightColor;
            ctx.fillRect(10, y + 6, 236, 4);
        };

        // Crease 1: Headrest / Upper Back boundary
        drawHorizontalCrease(150);

        // Crease 2: Upper Back / Mid-Lumbar boundary
        drawHorizontalCrease(310);

        // Segment 1 (Headrest): Curved top cushion with seat code tag
        const headGrad = ctx.createRadialGradient(128, 70, 10, 128, 70, 80);
        headGrad.addColorStop(0, highlightColor);
        headGrad.addColorStop(1, baseColor);
        ctx.fillStyle = headGrad;
        ctx.beginPath();
        ctx.roundRect(14, 14, 228, 126, 16);
        ctx.fill();

        // Silver / White Seat Code Badge
        ctx.fillStyle = state === 'selected' ? '#FFFFFF' : (state === 'crew' ? '#F59E0B' : '#E2E8F0');
        ctx.beginPath();
        ctx.roundRect(78, 62, 100, 34, 8);
        ctx.fill();
        ctx.strokeStyle = '#0F172A';
        ctx.lineWidth = 1.5;
        ctx.stroke();

        ctx.fillStyle = '#0F172A';
        ctx.font = 'bold 20px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        const label = state === 'crew' ? (seatNum === 1 ? 'CHAUFF' : 'CONVOY') : `S-${String(seatNum).padStart(2, '0')}`;
        ctx.fillText(label, 128, 79);

        // Segment 2 (Mid-Back Pillow): Soft radial cushion highlight
        const midGrad = ctx.createRadialGradient(128, 230, 15, 128, 230, 100);
        midGrad.addColorStop(0, highlightColor);
        midGrad.addColorStop(1, baseColor);
        ctx.fillStyle = midGrad;
        ctx.beginPath();
        ctx.roundRect(14, 162, 228, 138, 12);
        ctx.fill();

        // Segment 3 (Lower Lumbar Pillow): Pillowed bulge
        const lumGrad = ctx.createRadialGradient(128, 410, 15, 128, 410, 100);
        lumGrad.addColorStop(0, highlightColor);
        lumGrad.addColorStop(1, baseColor);
        ctx.fillStyle = lumGrad;
        ctx.beginPath();
        ctx.roundRect(14, 322, 228, 175, 12);
        ctx.fill();

        // Outer Lateral Bolster Shadows (giving contoured curved side wings)
        ctx.fillStyle = 'rgba(0,0,0,0.25)';
        ctx.fillRect(0, 0, 16, 512);
        ctx.fillRect(240, 0, 16, 512);

        const tex = new THREE.CanvasTexture(canvas);
        this.textureCache[key] = tex;
        return tex;
    }

    // 2. Procedural High-Res Quilted Leather Texture for Cushion (Seat Pan)
    generateSeatCushionTexture(state = 'available') {
        const key = `cushion_${state}`;
        if (this.textureCache[key]) return this.textureCache[key];

        const canvas = document.createElement('canvas');
        canvas.width = 256;
        canvas.height = 256;
        const ctx = canvas.getContext('2d');

        let baseColor = '#253545';
        let highlightColor = '#3b4e61';
        let shadowColor = '#15212c';

        if (state === 'selected') {
            baseColor = '#059669';
            highlightColor = '#10B981';
            shadowColor = '#047857';
        } else if (state === 'crew') {
            baseColor = '#0F2942';
            highlightColor = '#1E3A8A';
            shadowColor = '#0A192F';
        } else if (state === 'booked') {
            baseColor = '#475569';
            highlightColor = '#64748B';
            shadowColor = '#334155';
        }

        // Leather Base
        ctx.fillStyle = baseColor;
        ctx.fillRect(0, 0, 256, 256);

        // Center Indentation Contour
        const centerGrad = ctx.createRadialGradient(128, 128, 10, 128, 128, 110);
        centerGrad.addColorStop(0, highlightColor);
        centerGrad.addColorStop(0.8, baseColor);
        centerGrad.addColorStop(1, shadowColor);
        ctx.fillStyle = centerGrad;
        ctx.beginPath();
        ctx.roundRect(12, 12, 232, 232, 16);
        ctx.fill();

        // Rounded Waterfall Front Edge Highlight
        ctx.fillStyle = highlightColor;
        ctx.beginPath();
        ctx.roundRect(12, 220, 232, 24, 8);
        ctx.fill();

        // Double perimeter stitch
        ctx.strokeStyle = 'rgba(0,0,0,0.5)';
        ctx.lineWidth = 2;
        ctx.strokeRect(18, 18, 220, 220);

        const tex = new THREE.CanvasTexture(canvas);
        this.textureCache[key] = tex;
        return tex;
    }

    // 3. Front Fascia Texture (Swept-back multi-LED projector headlights, chrome grille, DRL eyebrow)
    generateFrontFasciaTexture() {
        const canvas = document.createElement('canvas');
        canvas.width = 1024;
        canvas.height = 512;
        const ctx = canvas.getContext('2d');

        // Pearl White Bumper Base
        ctx.fillStyle = '#F8FAFC';
        ctx.fillRect(0, 0, 1024, 512);

        // Lower Honeycomb Air Intake / Grille
        ctx.fillStyle = '#0F172A';
        ctx.beginPath();
        ctx.roundRect(280, 310, 464, 90, 8);
        ctx.fill();

        // Radiator Grille Horizontal Chrome Slats
        for (let y = 320; y <= 390; y += 10) {
            ctx.fillStyle = '#CBD5E1';
            ctx.fillRect(290, y, 444, 3);
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(290, y + 1, 444, 1.5);
        }

        // Center Chrome Emblem Blade
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(440, 350, 144, 10);
        ctx.fillStyle = '#0284C7';
        ctx.fillRect(480, 352, 64, 6);

        // Swept-back Headlight Clusters (Left and Right)
        const drawHeadlight = (isLeft) => {
            ctx.save();
            const startX = isLeft ? 110 : 914;
            const dir = isLeft ? 1 : -1;

            ctx.fillStyle = '#0F172A';
            ctx.beginPath();
            ctx.moveTo(startX, 240);
            ctx.lineTo(startX + (dir * 180), 200);
            ctx.lineTo(startX + (dir * 220), 280);
            ctx.lineTo(startX + (dir * 40), 320);
            ctx.closePath();
            ctx.fill();
            ctx.strokeStyle = '#CBD5E1';
            ctx.lineWidth = 3;
            ctx.stroke();

            // Upper Crystal LED Daytime Running Light (DRL) Eyebrow Strip
            ctx.strokeStyle = '#38BDF8';
            ctx.lineWidth = 8;
            ctx.shadowColor = '#38BDF8';
            ctx.shadowBlur = 12;
            ctx.beginPath();
            ctx.moveTo(startX + (dir * 10), 238);
            ctx.lineTo(startX + (dir * 180), 198);
            ctx.stroke();
            ctx.shadowBlur = 0;

            ctx.strokeStyle = '#FFFFFF';
            ctx.lineWidth = 4;
            ctx.beginPath();
            ctx.moveTo(startX + (dir * 10), 238);
            ctx.lineTo(startX + (dir * 180), 198);
            ctx.stroke();

            // Multi-Projector Xenon Lenses (3 Projectors)
            for (let p = 0; p < 3; p++) {
                const px = startX + (dir * (50 + p * 50));
                const py = 250 + (p * 8);

                ctx.fillStyle = '#94A3B8';
                ctx.beginPath();
                ctx.arc(px, py, 18, 0, Math.PI * 2);
                ctx.fill();

                const lensGrad = ctx.createRadialGradient(px, py, 2, px, py, 12);
                lensGrad.addColorStop(0, '#FFFFFF');
                lensGrad.addColorStop(0.5, '#E0F2FE');
                lensGrad.addColorStop(1, '#0284C7');
                ctx.fillStyle = lensGrad;
                ctx.beginPath();
                ctx.arc(px, py, 11, 0, Math.PI * 2);
                ctx.fill();
            }

            ctx.restore();
        };

        drawHeadlight(true);
        drawHeadlight(false);

        // License Plate Frame (LT-2026-RV)
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(442, 420, 140, 36);
        ctx.strokeStyle = '#0F172A';
        ctx.lineWidth = 3;
        ctx.strokeRect(442, 420, 140, 36);

        ctx.fillStyle = '#0F172A';
        ctx.font = 'bold 20px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('LT-2026-RV', 512, 446);

        return new THREE.CanvasTexture(canvas);
    }

    // 4. Side Livery Texture (Dynamic Marcopolo Wave Arch, Cyan/Sky-Blue Stripes, Red Pinstripe, Louvers & Latches)
    generateSideLiveryTexture(isRight = true) {
        const canvas = document.createElement('canvas');
        canvas.width = 2048;
        canvas.height = 512;
        const ctx = canvas.getContext('2d');

        // Pearl White Gloss Base
        ctx.fillStyle = '#F8FAFC';
        ctx.fillRect(0, 0, 2048, 512);

        // Dual Cyan & Sky-Blue Waistline Stripes
        ctx.fillStyle = '#38BDF8';
        ctx.fillRect(0, 190, 2048, 16);

        ctx.fillStyle = '#0284C7';
        ctx.fillRect(0, 210, 2048, 14);

        // Lower Red Accent Pinstripe
        ctx.fillStyle = '#EF4444';
        ctx.fillRect(0, 375, 2048, 6);

        // Signature Marcopolo B-Pillar "Aero Wave / Swoop" Arch
        ctx.save();
        if (isRight) {
            ctx.fillStyle = '#0F172A';
            ctx.beginPath();
            ctx.moveTo(1720, 0);
            ctx.bezierCurveTo(1680, 120, 1640, 230, 1560, 240);
            ctx.lineTo(1540, 270);
            ctx.bezierCurveTo(1630, 260, 1690, 150, 1750, 0);
            ctx.closePath();
            ctx.fill();

            ctx.strokeStyle = '#FFFFFF';
            ctx.lineWidth = 4;
            ctx.stroke();
        }
        ctx.restore();

        // Luggage Bay Doors with Cutlines & Chrome Latches
        const bayWidth = 260;
        const bayStartX = 420;
        for (let b = 0; b < 4; b++) {
            const bx = bayStartX + (b * (bayWidth + 14));
            ctx.strokeStyle = '#CBD5E1';
            ctx.lineWidth = 2.5;
            ctx.strokeRect(bx, 245, bayWidth, 125);

            ctx.fillStyle = '#94A3B8';
            ctx.fillRect(bx + 115, 255, 30, 14);
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(bx + 116, 256, 28, 4);
        }

        // Amber Clearance LED Marker Lights
        for (let m = 220; m < 1900; m += 240) {
            ctx.fillStyle = '#F59E0B';
            ctx.fillRect(m, 360, 14, 8);
        }

        // Rear Engine Cooling Louvers
        const louverX = 140;
        const louverY = 250;
        ctx.fillStyle = '#0F172A';
        ctx.fillRect(louverX, louverY, 110, 115);
        for (let ly = louverY + 8; ly < louverY + 110; ly += 9) {
            ctx.fillStyle = '#334155';
            ctx.fillRect(louverX + 6, ly, 98, 4);
        }

        return new THREE.CanvasTexture(canvas);
    }

    // 5. Deep-Dish Polished Chrome Rim Texture (10 radial cooling holes, lug nuts, cone hub)
    generateWheelRimTexture() {
        const canvas = document.createElement('canvas');
        canvas.width = 512;
        canvas.height = 512;
        const ctx = canvas.getContext('2d');
        const cx = 256, cy = 256;

        // Outer Chrome Rim Face
        const outerGrad = ctx.createRadialGradient(cx, cy, 140, cx, cy, 250);
        outerGrad.addColorStop(0, '#CBD5E1');
        outerGrad.addColorStop(0.5, '#F8FAFC');
        outerGrad.addColorStop(0.85, '#94A3B8');
        outerGrad.addColorStop(1, '#64748B');
        ctx.fillStyle = outerGrad;
        ctx.beginPath();
        ctx.arc(cx, cy, 250, 0, Math.PI * 2);
        ctx.fill();

        // Deep-Dish Recessed Inner Shadow
        const dishGrad = ctx.createRadialGradient(cx, cy, 40, cx, cy, 190);
        dishGrad.addColorStop(0, '#F1F5F9');
        dishGrad.addColorStop(0.7, '#94A3B8');
        dishGrad.addColorStop(1, '#334155');
        ctx.fillStyle = dishGrad;
        ctx.beginPath();
        ctx.arc(cx, cy, 190, 0, Math.PI * 2);
        ctx.fill();

        // 10 Radial Cooling Holes
        const numHoles = 10;
        for (let i = 0; i < numHoles; i++) {
            const angle = (i * Math.PI * 2) / numHoles;
            const hx = cx + Math.cos(angle) * 135;
            const hy = cy + Math.sin(angle) * 135;

            ctx.save();
            ctx.translate(hx, hy);
            ctx.rotate(angle);
            ctx.fillStyle = '#0F172A';
            ctx.beginPath();
            ctx.ellipse(0, 0, 22, 13, 0, 0, Math.PI * 2);
            ctx.fill();
            ctx.strokeStyle = '#FFFFFF';
            ctx.lineWidth = 2.5;
            ctx.stroke();
            ctx.restore();
        }

        // Central Polished Cone Hubcap
        const coneGrad = ctx.createRadialGradient(cx - 15, cy - 15, 5, cx, cy, 55);
        coneGrad.addColorStop(0, '#FFFFFF');
        coneGrad.addColorStop(0.6, '#94A3B8');
        coneGrad.addColorStop(1, '#475569');
        ctx.fillStyle = coneGrad;
        ctx.beginPath();
        ctx.arc(cx, cy, 55, 0, Math.PI * 2);
        ctx.fill();

        // Embossed RV Logo
        ctx.fillStyle = '#0284C7';
        ctx.beginPath();
        ctx.arc(cx, cy, 20, 0, Math.PI * 2);
        ctx.fill();

        ctx.fillStyle = '#FFFFFF';
        ctx.font = 'bold 15px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('RV', cx, cy);

        return new THREE.CanvasTexture(canvas);
    }

    /**
     * Build Photorealistic Cameroon Luxury Coach (Marcopolo New G7 / Scania)
     */
    buildPhotorealisticCoach() {
        const coachLength = this.totalSeats >= 80 ? 28.0 : 26.5;
        const coachWidth = 5.5;
        const coachHeight = 3.9;
        const frontZ = coachLength / 2;

        // PBR High-End Materials
        const pearlWhiteCarPaint = new THREE.MeshStandardMaterial({
            color: 0xf8fafc,
            metalness: 0.18,
            roughness: 0.14,
            envMap: this.envMap,
        });

        const pianoBlackGloss = new THREE.MeshStandardMaterial({
            color: 0x090d16,
            metalness: 0.85,
            roughness: 0.04,
            envMap: this.envMap,
        });

        const chromeMat = new THREE.MeshStandardMaterial({
            color: 0xffffff,
            metalness: 0.98,
            roughness: 0.04,
            envMap: this.envMap,
        });

        const darkChassisMat = new THREE.MeshStandardMaterial({
            color: 0x0f172a,
            roughness: 0.75,
            metalness: 0.2,
        });

        // Ultra-Clear Side Glass when cutaway is active so all seats shine brightly!
        const tintedGlassMat = new THREE.MeshPhysicalMaterial({
            color: 0x111c2e,
            transmission: 0.45,
            opacity: 0.28,
            transparent: true,
            roughness: 0.03,
            metalness: 0.2,
            ior: 1.52,
            envMap: this.envMap,
        });
        this.glassMaterial = tintedGlassMat;

        const roofMat = new THREE.MeshStandardMaterial({
            color: 0xf8fafc,
            roughness: 0.22,
            metalness: 0.1,
            envMap: this.envMap,
            transparent: true,
            opacity: 0.0, // Hidden by default for 100% unobstructed cabin view!
        });
        this.roofMaterial = roofMat;

        // --- 1. Main Sculpted Coach Lower Body (Luggage Deck & Skirt) ---
        const lowerBodyGeo = new THREE.BoxGeometry(coachWidth, 1.45, coachLength);
        const lowerBodyMesh = new THREE.Mesh(lowerBodyGeo, pearlWhiteCarPaint);
        lowerBodyMesh.position.set(0, 0.76, 0);
        lowerBodyMesh.castShadow = true;
        lowerBodyMesh.receiveShadow = true;
        this.coachGroup.add(lowerBodyMesh);

        // --- 2. Side Livery Panels with Marcopolo Wave Arch Texture ---
        const sidePanelGeo = new THREE.PlaneGeometry(coachLength, 1.85);

        const rightSideMat = new THREE.MeshStandardMaterial({
            map: this.generateSideLiveryTexture(true),
            roughness: 0.2,
            metalness: 0.1,
            envMap: this.envMap,
        });
        const rightSide = new THREE.Mesh(sidePanelGeo, rightSideMat);
        rightSide.position.set(coachWidth / 2 + 0.02, 1.25, 0);
        rightSide.rotation.y = Math.PI / 2;
        this.coachGroup.add(rightSide);

        const leftSideMat = new THREE.MeshStandardMaterial({
            map: this.generateSideLiveryTexture(false),
            roughness: 0.2,
            metalness: 0.1,
            envMap: this.envMap,
        });
        const leftSide = new THREE.Mesh(sidePanelGeo, leftSideMat);
        leftSide.position.set(-coachWidth / 2 - 0.02, 1.25, 0);
        leftSide.rotation.y = -Math.PI / 2;
        this.coachGroup.add(leftSide);

        // --- 3. Elevated High-Decker Passenger Deck Floor & Aisle Runners (Matching Photo) ---
        // Light Transit Grey Floor
        const deckGeo = new THREE.BoxGeometry(coachWidth - 0.25, 0.25, coachLength - 0.4);
        const deckMesh = new THREE.Mesh(deckGeo, new THREE.MeshStandardMaterial({ color: 0xcbd5e1, roughness: 0.6 }));
        deckMesh.position.set(0, 1.42, 0);
        deckMesh.receiveShadow = true;
        this.coachGroup.add(deckMesh);

        // Light Grey Central Transit Aisle (matching reference photo)
        const aisleGeo = new THREE.BoxGeometry(0.85, 0.02, coachLength - 2.8);
        const aisleMesh = new THREE.Mesh(aisleGeo, new THREE.MeshStandardMaterial({ color: 0xe2e8f0, roughness: 0.5 }));
        aisleMesh.position.set(0, 1.55, 0.2);
        this.coachGroup.add(aisleMesh);

        // Dual Illuminated White Safety Boundary Strips running along both aisle edges! (Clearly seen in photo)
        const stripGeo = new THREE.BoxGeometry(0.04, 0.025, coachLength - 2.8);
        const stripMat = new THREE.MeshStandardMaterial({
            color: 0xffffff,
            emissive: 0xf1f5f9,
            emissiveIntensity: 0.9,
        });

        const leftStrip = new THREE.Mesh(stripGeo, stripMat);
        leftStrip.position.set(-0.43, 1.56, 0.2);
        this.coachGroup.add(leftStrip);

        const rightStrip = new THREE.Mesh(stripGeo, stripMat);
        rightStrip.position.set(0.43, 1.56, 0.2);
        this.coachGroup.add(rightStrip);

        // Overhead Luggage Parcel Racks (Left and Right)
        const rackGeo = new THREE.BoxGeometry(1.6, 0.12, coachLength - 4.5);
        const rackMat = new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.3 });

        const leftRack = new THREE.Mesh(rackGeo, rackMat);
        leftRack.position.set(-coachWidth / 2 + 0.9, 3.45, -0.4);
        this.coachGroup.add(leftRack);

        const rightRack = new THREE.Mesh(rackGeo, rackMat);
        rightRack.position.set(coachWidth / 2 - 0.9, 3.45, -0.4);
        this.coachGroup.add(rightRack);

        // --- 4. Continuous Panoramic Tinted Side Glass Deck ---
        const glassGeo = new THREE.BoxGeometry(0.06, 1.7, coachLength - 3.8);

        const leftGlass = new THREE.Mesh(glassGeo, tintedGlassMat);
        leftGlass.position.set(-coachWidth / 2 + 0.03, 2.75, -0.4);
        this.coachGroup.add(leftGlass);

        const rightGlass = new THREE.Mesh(glassGeo, tintedGlassMat);
        rightGlass.position.set(coachWidth / 2 - 0.03, 2.75, -0.4);
        this.coachGroup.add(rightGlass);

        // --- 5. Aerodynamic Front Fascia (Photorealistic Bumper, Projector Headlights & Grille) ---
        const frontBumperGeo = new THREE.BoxGeometry(coachWidth + 0.04, 1.35, 1.2);
        const frontBumperMat = new THREE.MeshStandardMaterial({
            map: this.generateFrontFasciaTexture(),
            roughness: 0.15,
            metalness: 0.2,
            envMap: this.envMap,
        });
        const frontBumper = new THREE.Mesh(frontBumperGeo, frontBumperMat);
        frontBumper.position.set(0, 0.72, frontZ - 0.5);
        this.coachGroup.add(frontBumper);

        // Volumetric Xenon Headlight Spotlights
        const leftSpot = new THREE.SpotLight(0xf0f9ff, 0, 42, Math.PI / 4.8, 0.35, 1.2);
        leftSpot.position.set(-1.85, 0.75, frontZ + 0.2);
        leftSpot.target.position.set(-1.85, 0, frontZ + 20);
        this.scene.add(leftSpot);
        this.scene.add(leftSpot.target);
        this.headlightCones.push(leftSpot);

        const rightSpot = new THREE.SpotLight(0xf0f9ff, 0, 42, Math.PI / 4.8, 0.35, 1.2);
        rightSpot.position.set(1.85, 0.75, frontZ + 0.2);
        rightSpot.target.position.set(1.85, 0, frontZ + 20);
        this.scene.add(rightSpot);
        this.scene.add(rightSpot.target);
        this.headlightCones.push(rightSpot);

        // Front Piano-Black Aerodynamic Nose Cowl & Wipers
        const cowlGeo = new THREE.BoxGeometry(coachWidth - 0.25, 0.45, 0.5);
        const cowlMesh = new THREE.Mesh(cowlGeo, pianoBlackGloss);
        cowlMesh.position.set(0, 1.48, frontZ - 0.25);
        this.coachGroup.add(cowlMesh);

        // Dual-Curved Wrap-Around Panoramic Windshield
        const windshieldGeo = new THREE.PlaneGeometry(coachWidth - 0.35, 2.15);
        const windshield = new THREE.Mesh(windshieldGeo, tintedGlassMat);
        windshield.position.set(0, 2.65, frontZ - 0.78);
        windshield.rotation.x = 0.36;
        this.coachGroup.add(windshield);

        // Upper Roof Visor
        const visorGeo = new THREE.BoxGeometry(coachWidth - 0.2, 1.15, 1.35);
        const visorMesh = new THREE.Mesh(visorGeo, pianoBlackGloss);
        visorMesh.position.set(0, 3.45, frontZ - 0.85);
        this.coachGroup.add(visorMesh);

        // --- 6. Swept-Forward "Elephant-Ear" High-Mount Aero Mirrors ---
        this.createElephantEarMirror(-coachWidth / 2 - 0.4, 3.3, frontZ - 1.2, true);
        this.createElephantEarMirror(coachWidth / 2 + 0.4, 3.3, frontZ - 1.2, false);

        // --- 7. Deep-Dish Polished Chrome Tri-Axle Wheels (1 Front + 2 Rear Twin Axles) ---
        this.createPhotorealisticWheel(-coachWidth / 2 + 0.12, 0.58, frontZ - 3.8, false);
        this.createPhotorealisticWheel(coachWidth / 2 - 0.12, 0.58, frontZ - 3.8, true);

        const rearZ1 = -frontZ + 5.3;
        const rearZ2 = -frontZ + 3.1;

        this.createPhotorealisticWheel(-coachWidth / 2 + 0.12, 0.58, rearZ1, false);
        this.createPhotorealisticWheel(coachWidth / 2 - 0.12, 0.58, rearZ1, true);

        this.createPhotorealisticWheel(-coachWidth / 2 + 0.12, 0.58, rearZ2, false);
        this.createPhotorealisticWheel(coachWidth / 2 - 0.12, 0.58, rearZ2, true);

        // --- 8. Aerodynamic Roof Climate Fairing ---
        const roofGeo = new THREE.BoxGeometry(coachWidth, 0.28, coachLength);
        const roofMesh = new THREE.Mesh(roofGeo, roofMat);
        roofMesh.position.set(0, coachHeight + 0.52, 0);
        roofMesh.visible = false; // Hidden by default so chairs are 100% visible!
        this.coachGroup.add(roofMesh);
        this.roofMeshes.push(roofMesh);

        const acGeo = new THREE.BoxGeometry(2.6, 0.45, 5.2);
        const acMesh = new THREE.Mesh(acGeo, pearlWhiteCarPaint);
        acMesh.position.set(0, coachHeight + 0.78, 0);
        acMesh.visible = false;
        this.coachGroup.add(acMesh);
        this.roofMeshes.push(acMesh);

        // --- 9. BOTH Curbside Entrance Doors (Front Curbside & Middle Curbside) ---
        this.setupTwoCurbsideDoors(frontZ, coachWidth, pearlWhiteCarPaint, tintedGlassMat, chromeMat);

        // --- 10. Driver Cockpit on FRONT LEFT (Aligned with Seat 01) ---
        const is80 = this.totalSeats >= 78;
        const startZ = is80 ? 10.2 : 9.8;
        const dashGeo = new THREE.BoxGeometry(2.2, 0.85, 1.3);
        const dashMesh = new THREE.Mesh(dashGeo, new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.6 }));
        dashMesh.position.set(-1.5, 1.85, startZ + 1.4);
        this.coachGroup.add(dashMesh);

        const wheelTorusGeo = new THREE.TorusGeometry(0.3, 0.045, 14, 28);
        const steeringWheel = new THREE.Mesh(wheelTorusGeo, new THREE.MeshStandardMaterial({ color: 0x334155, metalness: 0.6 }));
        steeringWheel.position.set(-1.5, 2.2, startZ + 1.1);
        steeringWheel.rotation.x = -Math.PI / 3.2;
        this.coachGroup.add(steeringWheel);

        // --- 11. Photorealistic VIP Passenger Armchairs (Replicating User's Interior Photo) ---
        this.buildPhotorealisticVIPSeats(coachLength, coachWidth);

        this.scene.add(this.coachGroup);
    }

    /**
     * Deep-Dish Mirror-Polished Chrome Wheel with Radial Cutouts & Heavy-Duty Rubber Tire
     */
    createPhotorealisticWheel(x, y, z, isRight) {
        const wheelGroup = new THREE.Group();
        wheelGroup.position.set(x, y, z);

        const tireGeo = new THREE.CylinderGeometry(0.58, 0.58, 0.42, 32);
        const tireMat = new THREE.MeshStandardMaterial({
            color: 0x18181b,
            roughness: 0.85,
            metalness: 0.05,
        });
        const tire = new THREE.Mesh(tireGeo, tireMat);
        tire.rotation.z = Math.PI / 2;
        tire.castShadow = true;
        wheelGroup.add(tire);

        const rimGeo = new THREE.CylinderGeometry(0.44, 0.44, 0.44, 32);
        const rimMat = new THREE.MeshStandardMaterial({
            map: this.generateWheelRimTexture(),
            roughness: 0.06,
            metalness: 0.98,
            envMap: this.envMap,
        });
        const rim = new THREE.Mesh(rimGeo, rimMat);
        rim.rotation.z = Math.PI / 2;
        wheelGroup.add(rim);

        const coneGeo = new THREE.ConeGeometry(0.12, 0.14, 16);
        const coneMat = new THREE.MeshStandardMaterial({
            color: 0xffffff,
            metalness: 0.98,
            roughness: 0.04,
            envMap: this.envMap,
        });
        const cone = new THREE.Mesh(coneGeo, coneMat);
        cone.rotation.z = isRight ? Math.PI / 2 : -Math.PI / 2;
        cone.position.x = isRight ? 0.22 : -0.22;
        wheelGroup.add(cone);

        this.coachGroup.add(wheelGroup);
    }

    /**
     * Both Curbside Entrance Doors (Front & Middle) with 3-Step Illuminated Staircases
     */
    setupTwoCurbsideDoors(frontZ, coachWidth, bodyMat, glassMat, chromeMat) {
        const baseX = coachWidth / 2 - 0.04;
        const is80 = this.totalSeats >= 78;
        const startZ = is80 ? 10.2 : 9.8;
        const rowSpacing = 1.32;

        // Front Curbside Door at Row 4 (ENTRÉE 1)
        const frontDoorZ = startZ - (3 * rowSpacing);

        // Middle Curbside Door at Row 14 (80 seats) or Row 13 (75 seats) (ENTRÉE 2)
        const middleDoorRowIdx = is80 ? 13 : 12;
        const middleDoorZ = startZ - (middleDoorRowIdx * rowSpacing);

        this.doors.front = this.buildCurbsideDoor('front', baseX, frontDoorZ, bodyMat, glassMat, chromeMat);
        this.doorGroup = this.doors.front.group; // compatibility

        this.doors.middle = this.buildCurbsideDoor('middle', baseX, middleDoorZ, bodyMat, glassMat, chromeMat);
    }

    buildCurbsideDoor(name, baseX, baseZ, bodyMat, glassMat, chromeMat) {
        const doorHeight = 1.8;
        const doorWidth = 1.05;
        const baseY = 1.72;

        const frameMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.9 });
        const frameGeo = new THREE.BoxGeometry(0.04, doorHeight + 0.1, doorWidth + 0.1);
        const frame = new THREE.Mesh(frameGeo, frameMat);
        frame.position.set(baseX - 0.01, baseY, baseZ);
        this.coachGroup.add(frame);

        const doorGroup = new THREE.Group();
        doorGroup.position.set(baseX, baseY, baseZ);

        const leafGeo = new THREE.BoxGeometry(0.06, doorHeight, doorWidth);
        const leaf = new THREE.Mesh(leafGeo, bodyMat);
        leaf.castShadow = true;
        doorGroup.add(leaf);

        const cyanStripeGeo = new THREE.BoxGeometry(0.07, 0.08, doorWidth);
        const cyanStripe = new THREE.Mesh(cyanStripeGeo, new THREE.MeshStandardMaterial({ color: 0x38bdf8 }));
        cyanStripe.position.set(0, -0.15, 0);
        doorGroup.add(cyanStripe);

        const blueStripe = new THREE.Mesh(cyanStripeGeo, new THREE.MeshStandardMaterial({ color: 0x0284c7 }));
        blueStripe.position.set(0, -0.02, 0);
        doorGroup.add(blueStripe);

        const winGeo = new THREE.BoxGeometry(0.08, 0.78, 0.75);
        const win = new THREE.Mesh(winGeo, glassMat);
        win.position.set(0, 0.42, 0);
        doorGroup.add(win);

        const handleGeo = new THREE.BoxGeometry(0.09, 0.16, 0.06);
        const handle = new THREE.Mesh(handleGeo, chromeMat);
        handle.position.set(0.01, -0.28, -0.35);
        doorGroup.add(handle);

        this.coachGroup.add(doorGroup);

        // 3-Step Illuminated Boarding Staircase
        const stepMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.7 });
        const nosingMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b, emissive: 0xd97706, emissiveIntensity: 0.4 });

        const steps = [
            { y: 0.55, zOffset: 0.25 },
            { y: 0.85, zOffset: 0.0 },
            { y: 1.18, zOffset: -0.25 },
        ];

        steps.forEach(s => {
            const tread = new THREE.Mesh(new THREE.BoxGeometry(1.4, 0.12, 0.42), stepMat);
            tread.position.set(baseX - 0.7, s.y, baseZ + s.zOffset);
            this.coachGroup.add(tread);

            const nose = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.12, 0.42), nosingMat);
            nose.position.set(baseX - 0.01, s.y, baseZ + s.zOffset);
            this.coachGroup.add(nose);
        });

        const poleGeo = new THREE.CylinderGeometry(0.035, 0.035, 1.65, 14);
        const pole = new THREE.Mesh(poleGeo, chromeMat);
        pole.position.set(baseX - 0.25, baseY, baseZ + 0.44);
        this.coachGroup.add(pole);

        return {
            name: name,
            group: doorGroup,
            baseX: baseX,
            baseZ: baseZ,
        };
    }

    /**
     * Synchronized 4D Pneumatic Opening & Closing of BOTH Entrance Doors
     */
    toggleDoorAnimation() {
        this.isDoorOpen = !this.isDoorOpen;
        const targetZ = this.isDoorOpen ? -0.85 : 0;
        const targetX = this.isDoorOpen ? 0.32 : 0;

        if (!this.doors.front || !this.doors.middle) return;

        const frontStart = { x: this.doors.front.group.position.x, z: this.doors.front.group.position.z };
        const middleStart = { x: this.doors.middle.group.position.x, z: this.doors.middle.group.position.z };

        const frontBase = { x: this.doors.front.baseX, z: this.doors.front.baseZ };
        const middleBase = { x: this.doors.middle.baseX, z: this.doors.middle.baseZ };

        let progress = 0;
        const animateDoors = () => {
            progress += 0.08;
            if (progress <= 1) {
                this.doors.front.group.position.z = THREE.MathUtils.lerp(frontStart.z, frontBase.z + targetZ, progress);
                this.doors.front.group.position.x = THREE.MathUtils.lerp(frontStart.x, frontBase.x + targetX, progress);

                this.doors.middle.group.position.z = THREE.MathUtils.lerp(middleStart.z, middleBase.z + targetZ, progress);
                this.doors.middle.group.position.x = THREE.MathUtils.lerp(middleStart.x, middleBase.x + targetX, progress);

                requestAnimationFrame(animateDoors);
            } else {
                this.doors.front.group.position.z = frontBase.z + targetZ;
                this.doors.front.group.position.x = frontBase.x + targetX;

                this.doors.middle.group.position.z = middleBase.z + targetZ;
                this.doors.middle.group.position.x = middleBase.x + targetX;
            }
        };
        animateDoors();
    }

    createElephantEarMirror(x, y, z, isLeft) {
        const mirrorGroup = new THREE.Group();
        mirrorGroup.position.set(x, y, z);

        const bracketGeo = new THREE.CylinderGeometry(0.06, 0.06, 1.25);
        const bracketMat = new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.15, metalness: 0.2 });
        const bracket = new THREE.Mesh(bracketGeo, bracketMat);
        bracket.rotation.z = isLeft ? -0.32 : 0.32;
        mirrorGroup.add(bracket);

        const housingGeo = new THREE.BoxGeometry(0.35, 0.9, 0.24);
        const housing = new THREE.Mesh(housingGeo, bracketMat);
        housing.position.set(0, -0.42, 0.1);
        mirrorGroup.add(housing);

        const indGeo = new THREE.BoxGeometry(0.32, 0.08, 0.05);
        const indMat = new THREE.MeshStandardMaterial({ color: 0xf59e0b, emissive: 0xd97706, emissiveIntensity: 1.2 });
        const indicator = new THREE.Mesh(indGeo, indMat);
        indicator.position.set(0, -0.42, 0.23);
        mirrorGroup.add(indicator);

        const glassGeo = new THREE.PlaneGeometry(0.26, 0.8);
        const mirrorGlass = new THREE.Mesh(glassGeo, new THREE.MeshStandardMaterial({ color: 0x93c5fd, metalness: 0.98, roughness: 0.02 }));
        mirrorGlass.position.set(0, -0.42, -0.03);
        mirrorGlass.rotation.y = Math.PI;
        mirrorGroup.add(mirrorGlass);

        this.coachGroup.add(mirrorGroup);
    }

    /**
     * 100% Photorealistic VIP Passenger Armchairs (Replicating User's Interior Photo)
     * Features:
     * - Deep charcoal-navy quilted leather upholstery with 3 horizontal cushion segments.
     * - Lateral winged side bolsters curving forward.
     * - Modern two-tone armrests (clean white upright + dark leather pad).
     * - Twin tubular chrome pedestal legs.
     */
    getBlueprintSpecs() {
        const is80 = this.totalSeats >= 78;
        if (is80) {
            return [
                { row: 1, left: [null, 1, null], right: [2, 3], door: null },
                { row: 2, left: [4, 5, 6], right: [7, 8], door: null },
                { row: 3, left: [9, 10, 11], right: [12, 13], door: null },
                { row: 4, left: [14, 15, 16], right: [], door: 'ENTREE 1' },
                { row: 5, left: [17, 18, 19], right: [20, 21], door: null },
                { row: 6, left: [22, 23, 24], right: [25, 26], door: null },
                { row: 7, left: [27, 28, 29], right: [30, 31], door: null },
                { row: 8, left: [32, 33, 34], right: [35, 36], door: null },
                { row: 9, left: [37, 38, 39], right: [40, 41], door: null },
                { row: 10, left: [42, 43, 44], right: [45, 46], door: null },
                { row: 11, left: [47, 48, 49], right: [50, 51], door: null },
                { row: 12, left: [52, 53, 54], right: [55, 56], door: null },
                { row: 13, left: [57, 58, 59], right: [60, 61], door: null },
                { row: 14, left: [62, 63, 64], right: [], door: 'ENTREE 2' },
                { row: 15, left: [65, 66, 67], right: [68, 69], door: null },
                { row: 16, left: [70, 71, 72], right: [73, 74], door: null },
                { row: 17, left: [75, 76, 77], center: 78, right: [79, 80], door: null },
            ];
        }

        return [
            { row: 1, left: [null, 1, null], right: [2, 3], door: null },
            { row: 2, left: [4, 5, 6], right: [7, 8], door: null },
            { row: 3, left: [9, 10, 11], right: [12, 13], door: null },
            { row: 4, left: [14, 15, 16], right: [], door: 'ENTREE 1' },
            { row: 5, left: [17, 18, 19], right: [20, 21], door: null },
            { row: 6, left: [22, 23, 24], right: [25, 26], door: null },
            { row: 7, left: [27, 28, 29], right: [30, 31], door: null },
            { row: 8, left: [32, 33, 34], right: [35, 36], door: null },
            { row: 9, left: [37, 38, 39], right: [40, 41], door: null },
            { row: 10, left: [42, 43, 44], right: [45, 46], door: null },
            { row: 11, left: [47, 48, 49], right: [50, 51], door: null },
            { row: 12, left: [52, 53, 54], right: [55, 56], door: null },
            { row: 13, left: [57, 58, 59], right: [], door: 'ENTREE 2' },
            { row: 14, left: [60, 61, 62], right: [63, 64], door: null },
            { row: 15, left: [65, 66, 67], right: [68, 69], door: null },
            { row: 16, left: [70, 71, 72], center: 73, right: [74, 75], door: null },
        ];
    }

    /**
     * 100% Photorealistic VIP Passenger Armchairs (Carrefour 3+2 Blueprint Layout)
     */
    buildPhotorealisticVIPSeats(coachLength, coachWidth) {
        const is80 = this.totalSeats >= 78;
        const startZ = is80 ? 10.2 : 9.8;
        const rowSpacing = 1.32;
        const deckHeight = 1.55;

        // Column X coordinates:
        // Left side (3 seats): Col 1 (window: -2.15), Col 2 (middle: -1.50), Col 3 (aisle: -0.85)
        // Center Aisle (rear bench middle seat): 0.0
        // Right side (2 seats): Col 4 (aisle: +0.95), Col 5 (window: +1.75)
        const leftX = [-2.15, -1.50, -0.85];
        const rightX = [0.95, 1.75];
        const centerX = 0.0;

        // Ergonomic Geometries matching reference photo
        const backrestGeo = new THREE.BoxGeometry(0.55, 0.78, 0.12);
        const bolsterGeo = new THREE.BoxGeometry(0.08, 0.74, 0.16); // Forward-curving side wings
        const cushionGeo = new THREE.BoxGeometry(0.56, 0.18, 0.54);
        
        // Two-Tone Armrest Geometries (White upright post + Dark leather top pad)
        const armPostGeo = new THREE.BoxGeometry(0.05, 0.24, 0.06);
        const armPadGeo = new THREE.BoxGeometry(0.08, 0.05, 0.38);

        const armPostMat = new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.25, metalness: 0.1 });
        const armPadMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.45, metalness: 0.05 });
        const chromeLegMat = new THREE.MeshStandardMaterial({ color: 0xffffff, metalness: 0.92, roughness: 0.08 });
        const legGeo = new THREE.CylinderGeometry(0.025, 0.025, 0.32, 12);

        const specs = this.getBlueprintSpecs();

        const createSeat = (seatNum, x, z, outerArmX) => {
            const seatCode = 'S-' + String(seatNum).padStart(2, '0');
            const isCrew = (seatNum === 1 || seatNum === 16);

            let isBooked = false;
            if (Array.isArray(this.seatsData)) {
                const found = this.seatsData.find(s => s && (s.number === seatNum || s.code === seatCode));
                if (found && found.status === 'booked') isBooked = true;
            } else if (this.seatsData && typeof this.seatsData === 'object') {
                const s = this.seatsData[seatNum];
                if (s && s.status === 'booked') isBooked = true;
            }

            const isSelected = this.selectedSeats.has(seatCode);

            let state = 'available';
            if (isCrew) state = 'crew';
            else if (isBooked) state = 'booked';
            else if (isSelected) state = 'selected';

            const seatGroup = new THREE.Group();
            seatGroup.position.set(x, deckHeight, z);

            // Backrest Material with 3 Horizontal Quilted Segments
            const backrestMat = new THREE.MeshStandardMaterial({
                map: this.generateSeatBackrestTexture(seatNum, state),
                roughness: 0.32,
                metalness: 0.08,
            });

            // Seat Cushion Material with Deep Padding & Center Crease
            const cushionMat = new THREE.MeshStandardMaterial({
                map: this.generateSeatCushionTexture(state),
                roughness: 0.32,
                metalness: 0.08,
            });

            // 1. Seat Cushion (Seat Pan)
            const cushionMesh = new THREE.Mesh(cushionGeo, cushionMat);
            cushionMesh.position.set(0, 0.12, 0);
            cushionMesh.castShadow = true;
            cushionMesh.receiveShadow = true;
            seatGroup.add(cushionMesh);

            // 2. Ergonomic Backrest with 3 Quilted Cushion Segments
            const backrestMesh = new THREE.Mesh(backrestGeo, backrestMat);
            backrestMesh.position.set(0, 0.52, -0.19);
            backrestMesh.rotation.x = 0.08;
            backrestMesh.castShadow = true;
            seatGroup.add(backrestMesh);

            // 3. Ergonomic Lateral Bolsters (Winged Side Supports hugging torso)
            const leftBolster = new THREE.Mesh(bolsterGeo, backrestMat);
            leftBolster.position.set(-0.25, 0.52, -0.15);
            leftBolster.rotation.x = 0.08;
            seatGroup.add(leftBolster);

            const rightBolster = new THREE.Mesh(bolsterGeo, backrestMat);
            rightBolster.position.set(0.25, 0.52, -0.15);
            rightBolster.rotation.x = 0.08;
            seatGroup.add(rightBolster);

            // 4. Modern Two-Tone Armrest
            const armrestGroup = new THREE.Group();
            armrestGroup.position.set(0.29, 0.28, -0.05);

            const armPost = new THREE.Mesh(armPostGeo, armPostMat);
            armPost.position.set(0, -0.08, -0.08);
            armrestGroup.add(armPost);

            const armPad = new THREE.Mesh(armPadGeo, armPadMat);
            armPad.position.set(0, 0.05, 0);
            armrestGroup.add(armPad);

            seatGroup.add(armrestGroup);

            // Outer armrest if applicable
            if (outerArmX !== 0) {
                const outerArm = armrestGroup.clone();
                outerArm.position.x = outerArmX;
                seatGroup.add(outerArm);
            }

            // 5. Twin Polished Chrome Pedestal Floor Legs
            const leg1 = new THREE.Mesh(legGeo, chromeLegMat);
            leg1.position.set(-0.18, -0.06, -0.12);
            seatGroup.add(leg1);

            const leg2 = new THREE.Mesh(legGeo, chromeLegMat);
            leg2.position.set(0.18, -0.06, -0.12);
            seatGroup.add(leg2);

            // Selected Seat Initial Elevation Lift
            if (isSelected) {
                seatGroup.position.y = deckHeight + 0.18;
            }

            // Interactive Hitbox and Metadata
            seatGroup.userData = {
                seatCode: seatCode,
                seatNum: seatNum,
                isCrew: isCrew,
                isBooked: isBooked,
                cushionMesh: cushionMesh,
                backrestMesh: backrestMesh,
                leftBolster: leftBolster,
                rightBolster: rightBolster,
                worldPos: new THREE.Vector3(x, deckHeight + 0.7, z),
            };

            cushionMesh.userData = seatGroup.userData;
            backrestMesh.userData = seatGroup.userData;
            leftBolster.userData = seatGroup.userData;
            rightBolster.userData = seatGroup.userData;

            this.interactiveSeatObjects.push(cushionMesh, backrestMesh, leftBolster, rightBolster);
            this.seatMeshes[seatCode] = seatGroup;
            this.coachGroup.add(seatGroup);
        };

        specs.forEach((spec) => {
            const rowIdx = spec.row - 1;
            const z = startZ - (rowIdx * rowSpacing);

            // Left group (Col 1, 2, 3)
            spec.left.forEach((num, colIdx) => {
                if (num === null || num === undefined) return;
                const x = leftX[colIdx];
                const outerArm = (colIdx === 0) ? -0.31 : ((colIdx === 2) ? 0.31 : 0);
                createSeat(num, x, z, outerArm);
            });

            // Center seat on rear bench (Seat 78 in 80s or 73 in 75s)
            if (spec.center) {
                createSeat(spec.center, centerX, z, 0);
            }

            // Right group (Col 4, 5)
            spec.right.forEach((num, colIdx) => {
                if (num === null || num === undefined) return;
                const x = rightX[colIdx];
                const outerArm = (colIdx === 0) ? -0.31 : ((colIdx === 1) ? 0.31 : 0);
                createSeat(num, x, z, outerArm);
            });
        });
    }

    setupEvents() {
        this.container.addEventListener('mousemove', (e) => {
            const rect = this.container.getBoundingClientRect();
            this.mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
            this.mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;
            this.checkHover();
        });

        this.container.addEventListener('click', () => {
            this.handleSeatClick();
        });

        window.addEventListener('resize', () => {
            if (!this.container || !this.renderer || !this.camera) return;
            const w = this.container.clientWidth;
            const h = Math.min(Math.max(window.innerHeight * 0.65, 520), 660);
            this.camera.aspect = w / h;
            this.camera.updateProjectionMatrix();
            this.renderer.setSize(w, h);
        });

        // Theme Reactivity: Adjust WebGL ambient tones when light/dark theme toggles
        window.addEventListener('themeChanged', (e) => {
            const isDark = e.detail && e.detail.theme === 'dark';
            if (this.lights && this.lights.hemiLight) {
                this.lights.hemiLight.intensity = isDark ? 0.75 : 0.90;
            }
            if (this.lights && this.lights.interiorAmbient) {
                this.lights.interiorAmbient.intensity = isDark ? 0.65 : 0.85;
            }
        });
    }

    checkHover() {
        this.raycaster.setFromCamera(this.mouse, this.camera);
        const intersects = this.raycaster.intersectObjects(this.interactiveSeatObjects);

        if (intersects.length > 0) {
            const hit = intersects[0].object.userData;
            if (hit && hit.seatCode) {
                this.container.style.cursor = hit.isCrew || hit.isBooked ? 'not-allowed' : 'pointer';
                this.updateHoverTooltip(hit);
                return;
            }
        }

        this.container.style.cursor = 'grab';
        this.hideHoverTooltip();
    }

    handleSeatClick() {
        this.raycaster.setFromCamera(this.mouse, this.camera);
        const intersects = this.raycaster.intersectObjects(this.interactiveSeatObjects);

        if (intersects.length > 0) {
            const hit = intersects[0].object.userData;
            if (hit && hit.seatCode) {
                const isFr = this.locale === 'fr';
                if (hit.isCrew) {
                    alert(isFr 
                        ? "Ce fauteuil VIP est réservé strictement à l'équipage Real Voyage (Chauffeur / Convoyeur)."
                        : "This VIP seat is strictly reserved for Real Voyage crew (Driver / Attendant).");
                    return;
                }
                if (hit.isBooked) {
                    alert(isFr 
                        ? "Ce fauteuil est déjà réservé par un autre voyageur."
                        : "This seat has already been booked by another passenger.");
                    return;
                }

                this.toggleSeatSelection(hit.seatCode);
            }
        }
    }

    toggleSeatSelection(seatCode) {
        const isFr = this.locale === 'fr';
        if (this.selectedSeats.has(seatCode)) {
            if (this.selectedSeats.size === 1) {
                alert(isFr 
                    ? "Vous devez conserver au moins 1 fauteuil sélectionné."
                    : "You must keep at least 1 seat selected.");
                return;
            }
            this.selectedSeats.delete(seatCode);
        } else {
            if (this.selectedSeats.size >= 10) {
                alert(isFr 
                    ? "Vous pouvez sélectionner jusqu'à 10 fauteuils par réservation."
                    : "You can select up to 10 seats per booking.");
                return;
            }
            this.selectedSeats.add(seatCode);
        }

        this.refreshSeatVisuals();
        if (navigator.vibrate) navigator.vibrate(35);
        this.onSeatToggle(seatCode, Array.from(this.selectedSeats));
    }

    flyToFirstPersonSeat(seatCode) {
        const group = this.seatMeshes[seatCode];
        if (!group) return;

        const pos = group.userData.worldPos;
        this.setRoofTransparency(false);

        // Fly camera to passenger eye-level, looking forward towards the driver and windshield
        this.animateCameraPosition(pos.x, pos.y + 0.35, pos.z, pos.x, pos.y + 0.35, pos.z + 5);
        this.activeFirstPersonSeat = seatCode;
    }

    refreshSeatVisuals() {
        const deckHeight = 1.55;
        Object.keys(this.seatMeshes).forEach(code => {
            const group = this.seatMeshes[code];
            const u = group.userData;
            const isSel = this.selectedSeats.has(code);

            let state = 'available';
            if (u.isCrew) state = 'crew';
            else if (u.isBooked) state = 'booked';
            else if (isSel) state = 'selected';

            // Update Backrest and Cushion Textures
            const backTex = this.generateSeatBackrestTexture(u.seatNum, state);
            const cushTex = this.generateSeatCushionTexture(state);

            u.backrestMesh.material.map = backTex;
            u.backrestMesh.material.needsUpdate = true;

            u.cushionMesh.material.map = cushTex;
            u.cushionMesh.material.needsUpdate = true;

            if (u.leftBolster) {
                u.leftBolster.material.map = backTex;
                u.leftBolster.material.needsUpdate = true;
            }
            if (u.rightBolster) {
                u.rightBolster.material.map = backTex;
                u.rightBolster.material.needsUpdate = true;
            }

            // Elevation Lift on Selected Seats
            if (isSel) {
                group.position.y = deckHeight + 0.18;
            } else {
                group.position.y = deckHeight;
            }
        });
    }

    setCameraView(perspective) {
        this.activePerspective = perspective;

        if (perspective === 'interior') {
            // High-angle interior overview looking down the central aisle (Frames the exact user reference photo!)
            this.animateCameraPosition(0, 8.4, 4.2, 0, 1.8, 8.8);
            this.setRoofTransparency(true);
        } else if (perspective === 'isometric') {
            // Exterior 3D show angle
            this.animateCameraPosition(19, 9.5, 22, 0, 1.9, 0.5);
            this.setRoofTransparency(true);
        } else if (perspective === 'top') {
            // Birds-eye aerial plan view
            this.animateCameraPosition(0, 26, 0.1, 0, 1.8, 0);
            this.setRoofTransparency(true);
        } else if (perspective === 'cockpit') {
            // Front driver cockpit
            this.animateCameraPosition(-1.4, 2.6, 9.5, -1.4, 2.2, 16);
            this.setRoofTransparency(false);
        }
    }

    setRoofTransparency(transparent) {
        this.isRoofTransparent = transparent;
        if (this.roofMaterial) {
            this.roofMaterial.opacity = transparent ? 0.0 : 1.0;
            this.roofMaterial.depthWrite = !transparent;
        }
        if (this.glassMaterial) {
            this.glassMaterial.opacity = transparent ? 0.18 : 0.68;
        }
        this.roofMeshes.forEach(m => {
            m.visible = !transparent;
            m.castShadow = !transparent;
        });
    }

    animateCameraPosition(targetX, targetY, targetZ, lookX, lookY, lookZ) {
        if (!this.camera) return;
        if (!this.controls) {
            this.camera.position.set(targetX, targetY, targetZ);
            this.camera.lookAt(lookX, lookY, lookZ);
            return;
        }

        const startPos = this.camera.position.clone();
        const endPos = new THREE.Vector3(targetX, targetY, targetZ);
        const startTarget = this.controls.target ? this.controls.target.clone() : new THREE.Vector3(0, 1.8, 0);
        const endTarget = new THREE.Vector3(lookX, lookY, lookZ);

        let progress = 0;
        const animateStep = () => {
            progress += 0.05;
            if (progress <= 1) {
                this.camera.position.lerpVectors(startPos, endPos, progress);
                if (this.controls && this.controls.target) {
                    this.controls.target.lerpVectors(startTarget, endTarget, progress);
                }
                requestAnimationFrame(animateStep);
            } else {
                this.camera.position.copy(endPos);
                if (this.controls && this.controls.target) {
                    this.controls.target.copy(endTarget);
                }
            }
        };
        animateStep();
    }

    updateHoverTooltip(seatData) {
        const tip = document.getElementById('threeSeatHud');
        if (!tip) return;

        const code = seatData.seatCode;
        const isSel = this.selectedSeats.has(code);

        document.getElementById('threeHudCode').textContent = code;
        const statusEl = document.getElementById('threeHudStatus');

        if (seatData.isCrew) {
            statusEl.textContent = "Équipage Real Voyage";
            statusEl.className = "hud-pill hud-pill-crew";
        } else if (seatData.isBooked) {
            statusEl.textContent = "Déjà Réservé";
            statusEl.className = "hud-pill hud-pill-booked";
        } else if (isSel) {
            statusEl.textContent = "Votre Choix";
            statusEl.className = "hud-pill hud-pill-selected";
        } else {
            statusEl.textContent = "Disponible (Cliquer pour choisir)";
            statusEl.className = "hud-pill hud-pill-available";
        }

        tip.style.display = 'flex';
    }

    hideHoverTooltip() {
        const tip = document.getElementById('threeSeatHud');
        if (tip) tip.style.display = 'none';
    }

    animate() {
        requestAnimationFrame(this.animate);
        if (this.controls) this.controls.update();
        if (this.renderer && this.scene && this.camera) {
            this.renderer.render(this.scene, this.camera);
        }
    }
}

window.RealVoyageBus3D = RealVoyageBus3D;
