<?php
require_once __DIR__ . '/config/path.php';
require_once __DIR__ . '/config/auth.php';

$page_title = 'Tentang Kami - Veloce';
include __DIR__ . '/partials/header.php';
?>

<style>
/* About Hero Section */
.about-hero {
    background: linear-gradient(135deg, #1f3b83 0%, #5b8af0 100%);
    padding: 60px 20px;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
}

.about-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,144C960,149,1056,139,1152,122.7C1248,107,1344,85,1392,74.7L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
    background-size: cover;
    background-position: bottom;
    opacity: 0.3;
}

.about-hero h1 {
    font-size: 36px;
    font-weight: 800;
    margin-bottom: 15px;
    position: relative;
    z-index: 1;
}

.about-hero p {
    font-size: 16px;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.5;
    position: relative;
    z-index: 1;
}

/* Story Section */
.story-section {
    padding: 50px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.story-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: center;
    margin-bottom: 40px;
}

.story-content h2 {
    font-size: 28px;
    color: #1f3b83;
    margin-bottom: 15px;
    font-weight: 800;
}

.story-content p {
    font-size: 15px;
    line-height: 1.6;
    color: #555;
    margin-bottom: 12px;
}

.story-image {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(31, 59, 131, 0.2);
}

.story-image img {
    width: 100%;
    height: 300px;
    object-fit: cover;
    display: block;
}

/* Values Section */
.values-section {
    background: linear-gradient(135deg, #f8faff 0%, #ecf0ff 100%);
    padding: 50px 20px;
}

.values-container {
    max-width: 1200px;
    margin: 0 auto;
}

.values-container h2 {
    text-align: center;
    font-size: 28px;
    color: #1f3b83;
    margin-bottom: 35px;
    font-weight: 800;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
}

.value-card {
    background: white;
    padding: 25px 20px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s, box-shadow 0.3s;
}

.value-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(31, 59, 131, 0.15);
}

.value-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #1f3b83 0%, #5b8af0 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 26px;
    color: white;
}

.value-card h3 {
    font-size: 18px;
    color: #1f3b83;
    margin-bottom: 10px;
    font-weight: 700;
}

.value-card p {
    font-size: 14px;
    color: #666;
    line-height: 1.5;
}

/* Team Section */
.team-section {
    padding: 50px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.team-section h2 {
    text-align: center;
    font-size: 28px;
    color: #1f3b83;
    margin-bottom: 35px;
    font-weight: 800;
}

.team-grid {
    display: flex;
    justify-content: center;
    gap: 40px;
    flex-wrap: wrap;
}

.team-member {
    text-align: center;
}

.team-photo {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    margin: 0 auto 15px;
    background: linear-gradient(135deg, #1f3b83 0%, #5b8af0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: white;
    font-weight: 800;
    box-shadow: 0 10px 30px rgba(31, 59, 131, 0.2);
}

.team-member h3 {
    font-size: 16px;
    color: #1f3b83;
    margin-bottom: 3px;
    font-weight: 700;
}

.team-member p {
    font-size: 13px;
    color: #666;
}

/* CTA Section */
.cta-section {
    background: linear-gradient(135deg, #1f3b83 0%, #5b8af0 100%);
    padding: 50px 20px;
    text-align: center;
    color: white;
}

.cta-section h2 {
    font-size: 28px;
    margin-bottom: 15px;
    font-weight: 800;
}

.cta-section p {
    font-size: 16px;
    margin-bottom: 25px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.cta-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-white {
    background: white;
    color: #1f3b83;
    padding: 12px 30px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 700;
    font-size: 15px;
    transition: transform 0.3s, box-shadow 0.3s;
    display: inline-block;
}

.btn-white:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.btn-outline-white {
    background: transparent;
    color: white;
    padding: 15px 40px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 700;
    font-size: 18px;
    border: 2px solid white;
    transition: background 0.3s, color 0.3s;
    display: inline-block;
}

.btn-outline-white:hover {
    background: white;
    color: #1f3b83;
}

/* Responsive */
@media (max-width: 768px) {
    .about-hero h1 {
        font-size: 36px;
    }
    
    .about-hero p {
        font-size: 16px;
    }
    
    .story-grid {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .story-content h2,
    .values-container h2,
    .team-section h2,
    .cta-section h2 {
        font-size: 32px;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<!-- Hero Section -->
<section class="about-hero">
    <h1>Tentang Veloce</h1>
    <p>Menyediakan perlengkapan sepak bola berkualitas tinggi untuk para atlet dan penggemar olahraga di seluruh Indonesia</p>
</section>

<!-- Story Section -->
<section class="story-section">
    <div class="story-grid">
        <div class="story-content">
            <h2>Cerita Kami</h2>
            <p>Veloce didirikan dengan satu misi sederhana: membuat perlengkapan sepak bola berkualitas tinggi dapat diakses oleh semua orang.</p>
            <p>Berawal dari passion terhadap sepak bola, kami memahami pentingnya memiliki peralatan yang tepat untuk meningkatkan performa di lapangan.</p>
            <p>Sejak tahun 2020, kami telah melayani ribuan pelanggan dengan produk-produk pilihan dari brand ternama dunia.</p>
        </div>
        <div class="story-image">
            <img src="<?php echo url('assets/img/about-story.jpg'); ?>" 
                 alt="Veloce Story" 
                 onerror="this.src='https://images.unsplash.com/photo-1579952363873-27f3bade9f55?w=800&h=600&fit=crop'">
        </div>
    </div>

    <div class="story-grid" style="direction: rtl;">
        <div class="story-content" style="direction: ltr;">
            <h2>Visi Kami</h2>
            <p>Menjadi toko perlengkapan sepak bola terpercaya di Indonesia yang menghadirkan produk berkualitas dengan harga terjangkau.</p>
            <p>Kami berkomitmen untuk terus berinovasi dan memberikan pengalaman berbelanja terbaik bagi setiap pelanggan.</p>
        </div>
        <div class="story-image" style="direction: ltr;">
            <img src="<?php echo url('assets/img/about-vision.jpg'); ?>" 
                 alt="Veloce Vision" 
                 onerror="this.src='https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800&h=600&fit=crop'">
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="values-section">
    <div class="values-container">
        <h2>Nilai-Nilai Kami</h2>
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Kualitas Terjamin</h3>
                <p>Semua produk kami adalah original dan telah melalui quality control ketat untuk memastikan kepuasan Anda.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Pelayanan Terbaik</h3>
                <p>Tim kami siap membantu Anda dengan ramah dan profesional untuk pengalaman berbelanja yang menyenangkan.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-truck-fast"></i>
                </div>
                <h3>Pengiriman Cepat</h3>
                <p>Kami bekerja sama dengan kurir terpercaya untuk memastikan pesanan Anda sampai dengan aman dan tepat waktu.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-tag"></i>
                </div>
                <h3>Harga Kompetitif</h3>
                <p>Dapatkan produk berkualitas dengan harga terbaik. Kami berkomitmen memberikan value terbaik untuk Anda.</p>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team-section">
    <h2>Tim Kami</h2>
    <div class="team-grid">
        <div class="team-member">
            <div class="team-photo">Z</div>
            <h3>Zinedine Zidane</h3>
            <p>Founder & CEO</p>
        </div>
        
        <div class="team-member">
            <div class="team-photo">A</div>
            <h3>Antoine Griezmann</h3>
            <p>Customer Service</p>
        </div>
        
        <div class="team-member">
            <div class="team-photo">A</div>
            <h3>Andrea Pirlo</h3>
            <p>Warehouse Manager</p>
        </div>
        
        <div class="team-member">
            <div class="team-photo">G</div>
            <h3>Gianluigi Buffon</h3>
            <p>Marketing</p>
        </div>
    </div>
</section>

<!-- CTA Section -->
<?php if (!isAdmin()): ?>
<section class="cta-section">
    <h2>Siap Meningkatkan Performa Anda?</h2>
    <p>Jelajahi koleksi lengkap perlengkapan sepak bola kami dan temukan produk yang sempurna untuk kebutuhan Anda</p>
    <div class="cta-buttons">
        <a href="<?php echo url('produk.php'); ?>" class="btn-white">
            <i class="fas fa-shopping-bag"></i> Belanja Sekarang
        </a>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
