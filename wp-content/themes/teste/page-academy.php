<?php
/**
 * Template Name: Academy
 * Descrição: Página de Vídeos Academy - Poker111
 */

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
$usuario_logado = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// ── Busca dados da Academy via API ────────────────────────────────
$ACADEMY_BASE = 'https://backoffice.poker111.digital';
$ACADEMY_KEY  = 'e131d72bd349ed9fd996282a2a155f8b348150a2dec8f732fc834168be3e7a23';

function academy_api_get(string $base, string $key, string $endpoint): ?array
{
    $ch = curl_init($base . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => ['X-Api-Key: ' . $key],
    ]);
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code !== 200 || !$response) return null;
    $body = json_decode($response, true);
    return (!empty($body['success']) && isset($body['data'])) ? $body['data'] : null;
}

function academy_youtube_id(string $url): string
{
    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_\-]{11})/', $url, $m)) {
        return $m[1];
    }
    return '';
}

$academy_categories = academy_api_get($ACADEMY_BASE, $ACADEMY_KEY, '/api/academy/categories') ?? [];
$academy_videos     = academy_api_get($ACADEMY_BASE, $ACADEMY_KEY, '/api/academy/videos')     ?? [];

get_header();
?>

<!-- Variáveis para JS -->
<script>
    var usuarioLogado = <?php echo $usuario_logado ? 'true' : 'false'; ?>;
</script>

<!-- ===== ACADEMY PAGE ===== -->
<section class="academy-section">
    <div class="academy-container">

        <!-- Hero Header -->
        <div class="academy-header">
            <div class="academy-badge">
                <i class="fas fa-play-circle"></i>
                Video Library
            </div>
            <h1 class="academy-title">Poker <span class="academy-title-highlight">Academy</span></h1>
            <p class="academy-subtitle">Master your game with exclusive video lessons from professional players and coaches.</p>
        </div>

        <!-- Stats Bar -->
        <div class="academy-stats">
            <div class="academy-stat">
                <span class="academy-stat-value"><?= count($academy_videos) ?></span>
                <span class="academy-stat-label">Videos</span>
            </div>
            <div class="academy-stat">
                <span class="academy-stat-value"><?= count($academy_categories) ?></span>
                <span class="academy-stat-label">Categories</span>
            </div>
            <div class="academy-stat">
                <span class="academy-stat-value">Free</span>
                <span class="academy-stat-label">Access</span>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="academy-filters">
            <button class="academy-filter-btn active" data-filter="all">All Videos</button>
            <?php foreach ($academy_categories as $cat): ?>
                <button class="academy-filter-btn" data-filter="<?= esc_attr(strtolower($cat['nome'])) ?>">
                    <?= esc_html($cat['nome']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Video Grid -->
        <div class="academy-grid" id="academyGrid">

            <?php if (empty($academy_videos)): ?>
                <p style="color:#ccc;text-align:center;grid-column:1/-1;">Nenhum vídeo encontrado.</p>
            <?php else: ?>
            <?php foreach ($academy_videos as $video):
                $yt_id    = academy_youtube_id($video['link_video'] ?? '');
                $thumb    = $yt_id ? 'https://img.youtube.com/vi/' . $yt_id . '/maxresdefault.jpg' : '';
                $thumb_fb = $yt_id ? 'https://img.youtube.com/vi/' . $yt_id . '/hqdefault.jpg'    : '';
                $titulo   = $video['titulo']      ?? '';
                $descricao= $video['descricao']   ?? '';
                $cat_nome = $video['category_nome'] ?? '';
                $cat_slug = strtolower($cat_nome);
            ?>
            <article class="academy-card"
                     data-category="<?= esc_attr($cat_slug) ?>"
                     data-video-id="<?= esc_attr($yt_id) ?>"
                     data-title="<?= esc_attr($titulo) ?>"
                     data-description="<?= esc_attr($descricao) ?>">
                <div class="academy-card-thumbnail" onclick="openVideoModal(this.closest('.academy-card'))">
                    <?php if ($thumb): ?>
                        <img src="<?= esc_url($thumb) ?>" alt="<?= esc_attr($titulo) ?>"
                             onerror="this.src='<?= esc_url($thumb_fb) ?>'">
                    <?php endif; ?>
                    <div class="academy-play-btn"><i class="fas fa-play"></i></div>
                    <div class="academy-card-category"><?= esc_html($cat_nome) ?></div>
                </div>
                <div class="academy-card-content">
                    <h2 class="academy-card-title"><?= esc_html($titulo) ?></h2>
                    <?php if ($descricao): ?>
                        <p class="academy-card-excerpt"><?= esc_html($descricao) ?></p>
                    <?php endif; ?>
                    <div class="academy-card-meta">
                        <button class="academy-watch-btn" onclick="openVideoModal(this.closest('.academy-card'))">
                            <i class="fas fa-play"></i> Watch Now
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
            <?php endif; ?>

        </div><!-- /.academy-grid -->

        <!-- Empty State (shown by JS when all cards filtered out) -->
        <div class="academy-empty" id="academyEmpty">
            <div class="academy-empty-icon"><i class="fas fa-video-slash"></i></div>
            <p class="academy-empty-text">No videos found in this category.</p>
        </div>

    </div><!-- /.academy-container -->
</section>

<!-- ===== VIDEO MODAL ===== -->
<div class="academy-modal-overlay" id="academyModal" onclick="closeVideoModal(event)">
    <div class="academy-modal">
        <div class="academy-modal-header">
            <div class="academy-modal-info">
                <span class="academy-modal-category" id="modalCategory">Strategy</span>
                <h2 class="academy-modal-title" id="modalTitle">Video Title</h2>
            </div>
            <button class="academy-modal-close" onclick="closeVideoModal(null, true)" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="academy-modal-video">
            <iframe
                id="academyVideoFrame"
                src=""
                title="YouTube video player"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen>
            </iframe>
        </div>
        <p class="academy-modal-desc" id="modalDesc"></p>
    </div>
</div>

<!-- Modal de Usuário Não Logado -->
<div id="notLoggedModal" class="modal not-logged-modal">
    <div class="modal-content">
        <div class="modal-top-bar">
            <img src="<?php echo get_template_directory_uri(); ?>/logopoker.png" alt="POKER III" class="modal-logo">
        </div>

        <div class="modal-header">
            <div class="not-logged-icon">
                <i class="fas fa-user-lock"></i>
            </div>
            <h2 class="modal-title" id="notLoggedTitle">Oops! You're not logged in</h2>
            <p class="modal-subtitle" id="notLoggedSubtitle">We noticed you're not logged in. Please log in to access the Academy and all features.</p>
        </div>

        <div class="not-logged-actions">
            <button class="btn-login-now" onclick="openLoginFromNotLogged()">
                <i class="fas fa-sign-in-alt"></i>
                <span id="notLoggedLoginBtn">Log In</span>
            </button>
            <p class="not-logged-signup-text">
                <span id="notLoggedNoAccount">Don't have an account?</span>
                <a href="#" onclick="openSignupFromNotLogged()" id="notLoggedSignupLink">Sign Up</a>
            </p>
        </div>
    </div>
</div>

<!-- JS -->
<script src="<?php echo get_template_directory_uri(); ?>/js/academy.js"></script>

<?php get_footer(); ?>
