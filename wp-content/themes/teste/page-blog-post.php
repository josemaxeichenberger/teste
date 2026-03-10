<?php
/**
 * Template Name: Blog Post
 * Descrição: Página de Post Individual do Blog - Poker111
 */

// ── Busca post individual da API ──────────────────────────────────────────────
define('POKER111_API_BASE', 'https://backoffice.poker111.digital/api/blog');
define('POKER111_KEY',      'e131d72bd349ed9fd996282a2a155f8b348150a2dec8f732fc834168be3e7a23');

function poker111_curl(string $endpoint): ?array {
    $ch = curl_init(POKER111_API_BASE . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => ['X-API-Key: ' . POKER111_KEY],
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200 || !$resp) return null;
    $body = json_decode($resp, true);
    return (!empty($body['success']) && !empty($body['data'])) ? $body['data'] : null;
}

// ── Converte Markdown para HTML ─────────────────────────────────────────────
function poker111_md_to_html(string $md): string {
    $md = htmlspecialchars($md, ENT_NOQUOTES, 'UTF-8');

    // Blocos de código
    $md = preg_replace('/```[\w]*\n?([\s\S]*?)```/', '<pre><code>$1</code></pre>', $md);
    $md = preg_replace('/`([^`]+)`/', '<code>$1</code>', $md);

    // Headings
    $md = preg_replace('/^######\s+(.+)$/m', '<h6>$1</h6>', $md);
    $md = preg_replace('/^#####\s+(.+)$/m',  '<h5>$1</h5>', $md);
    $md = preg_replace('/^####\s+(.+)$/m',   '<h4>$1</h4>', $md);
    $md = preg_replace('/^###\s+(.+)$/m',    '<h3>$1</h3>', $md);
    $md = preg_replace('/^##\s+(.+)$/m',     '<h2>$1</h2>', $md);
    $md = preg_replace('/^#\s+(.+)$/m',      '<h1>$1</h1>', $md);

    // Negrito e itálico
    $md = preg_replace('/\*\*\*(.+?)\*\*\*/', '<strong><em>$1</em></strong>', $md);
    $md = preg_replace('/\*\*(.+?)\*\*/',     '<strong>$1</strong>', $md);
    $md = preg_replace('/\*(.+?)\*/',         '<em>$1</em>', $md);

    // Linha horizontal
    $md = preg_replace('/^[-*_]{3,}\s*$/m', '<hr>', $md);

    // Listas não ordenadas
    $md = preg_replace_callback('/(?:^[\*\-]\s+.+\n?)+/m', function($m) {
        $items = preg_replace('/^[\*\-]\s+(.+)$/m', '<li>$1</li>', trim($m[0]));
        return '<ul>' . $items . '</ul>';
    }, $md);

    // Listas ordenadas
    $md = preg_replace_callback('/(?:^\d+\.\s+.+\n?)+/m', function($m) {
        $items = preg_replace('/^\d+\.\s+(.+)$/m', '<li>$1</li>', trim($m[0]));
        return '<ol>' . $items . '</ol>';
    }, $md);

    // Links e imagens
    $md = preg_replace('/!\[([^\]]*?)\]\(([^)]+?)\)/', '<img src="$2" alt="$1" style="max-width:100%">', $md);
    $md = preg_replace('/\[([^\]]+?)\]\(([^)]+?)\)/',  '<a href="$2" target="_blank" rel="noopener">$1</a>', $md);

    // Parágrafos: quebra linhas duplas
    $blocos = preg_split('/\n{2,}/', trim($md));
    $html   = '';
    foreach ($blocos as $bloco) {
        $bloco = trim($bloco);
        if ($bloco === '') continue;
        if (preg_match('/^<(h[1-6]|ul|ol|li|hr|pre|blockquote|img)/i', $bloco)) {
            $html .= $bloco . "\n";
        } else {
            $html .= '<p>' . nl2br($bloco) . "</p>\n";
        }
    }
    return $html;
}

$slug        = sanitize_text_field($_GET['slug'] ?? '');
$post        = $slug ? poker111_curl('/posts/' . urlencode($slug)) : null;
$all_posts   = poker111_curl('/posts') ?? [];
$other_posts = array_filter($all_posts, fn($p) => ($p['slug'] ?? '') !== $slug);
$other_posts = array_slice(array_values($other_posts), 0, 3);

$post_title   = esc_html($post['title'] ?? 'Post não encontrado');
// excerpt: remove Markdown e trunca
$excerpt_raw  = preg_replace('/^#{1,6}\s+.+$/m', '', $post['excerpt'] ?? '');
$excerpt_raw  = preg_replace('/[*`_>\[\]]+/', '', $excerpt_raw);
$excerpt_raw  = trim(preg_replace('/\s{2,}/', ' ', $excerpt_raw));
$post_excerpt = esc_html($excerpt_raw);
$post_date    = !empty($post['created_at']) ? date('M j, Y', strtotime($post['created_at'])) : '';
$post_image   = esc_url($post['featured_image'] ?? '');
// content: converte Markdown → HTML
$post_content = !empty($post['content']) ? poker111_md_to_html($post['content']) : '';

get_header();
?>

<!-- Hero do Post -->
<section class="blog-post-hero">
    <div class="blog-post-hero-content">
        <h1 class="blog-post-hero-title"><?= $post_title ?></h1>
        <?php if ($post_date): ?><p class="blog-post-hero-date"><?= $post_date ?></p><?php endif; ?>
        <?php if ($post_excerpt): ?><p class="blog-post-hero-excerpt"><?= $post_excerpt ?></p><?php endif; ?>
    </div>
</section>

<!-- Container Branco do Post -->
<section class="blog-post-content">
    <div class="blog-post-content-inner">
        <?php if ($post_image): ?>
        <div class="blog-post-featured-image">
            <img src="<?= $post_image ?>" alt="<?= $post_title ?>">
        </div>
        <?php endif; ?>
        <?php if ($post_content): ?>
            <div class="blog-post-body"><?= $post_content ?></div>
        <?php else: ?>
            <div class="blog-post-body"><p style="color:#999;">Conteúdo não disponível.</p></div>
        <?php endif; ?>
    </div>
</section>

<!-- Other News -->
<?php if (!empty($other_posts)): ?>
<section class="blog-other-news-header">
    <h2 class="blog-other-news-title">Other News</h2>
    <p class="blog-other-news-subtitle">Stay updated with industry insights and company developments</p>

    <div class="blog-grid blog-other-news-grid">
        <?php foreach ($other_posts as $op):
            $op_slug        = esc_attr($op['slug'] ?? '');
            $op_title       = esc_html($op['title'] ?? '');
            $op_exc_raw     = preg_replace('/^#{1,6}\s+.+$/m', '', $op['excerpt'] ?? '');
            $op_exc_raw     = preg_replace('/[*`_>\[\]]+/', '', $op_exc_raw);
            $op_exc_raw     = trim(preg_replace('/\s{2,}/', ' ', $op_exc_raw));
            $op_excerpt     = esc_html(mb_strlen($op_exc_raw) > 120 ? mb_substr($op_exc_raw, 0, 120) . '...' : $op_exc_raw);
            $op_image       = esc_url($op['featured_image'] ?? '');
            $op_date        = !empty($op['created_at']) ? date('M j, Y', strtotime($op['created_at'])) : '';
            $op_url         = esc_url(home_url('/blog-post/') . '?slug=' . $op_slug);
        ?>
        <article class="blog-card">
            <div class="blog-card-image">
                <?php if ($op_image): ?>
                    <img src="<?= $op_image ?>" alt="<?= $op_title ?>">
                <?php else: ?>
                    <img src="<?php echo get_template_directory_uri(); ?>/imagens/blog/ece298d0ec2c16f10310d45724b276a6035cb503.png" alt="<?= $op_title ?>">
                <?php endif; ?>
            </div>
            <div class="blog-card-content">
                <?php if ($op_date): ?><p class="blog-card-date"><?= $op_date ?></p><?php endif; ?>
                <h2 class="blog-card-title"><?= $op_title ?></h2>
                <?php if ($op_excerpt): ?><p class="blog-card-excerpt"><?= $op_excerpt ?></p><?php endif; ?>
                <a href="<?= $op_url ?>" class="blog-card-link">Read More →</a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Seção Blog -->
<section class="blog-section">
    <div class="blog-container">

      

    </div>
</section>

<?php get_footer(); ?>
