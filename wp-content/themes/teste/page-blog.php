<?php
/**
 * Template Name: Blog
 * Descrição: Página de Blog - Poker111
 */

get_header();
?>

<!-- Seção Blog -->
<section class="blog-section">
    <div class="blog-container">

        <!-- Título -->
        <h1 class="blog-title">Blog</h1>

        <!-- Grid de Artigos -->
        <?php
        // ── Busca artigos da API Poker111 ──────────────────────────────────────
        $api_url = 'https://backoffice.poker111.digital/api/blog/posts';
        $api_key = 'e131d72bd349ed9fd996282a2a155f8b348150a2dec8f732fc834168be3e7a23';

        $ch = curl_init($api_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => ['X-API-Key: ' . $api_key],
        ]);
        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $posts = [];
        if ($http_code === 200 && $response) {
            $body = json_decode($response, true);
            if (!empty($body['success']) && !empty($body['data'])) {
                $posts = $body['data'];
            }
        }
        ?>

        <div class="blog-grid">

            <?php if (empty($posts)): ?>
                <p style="color:#ccc;text-align:center;grid-column:1/-1;">Nenhum artigo encontrado.</p>
            <?php else: ?>
                <?php foreach ($posts as $post):
                    $slug    = esc_attr($post['slug'] ?? '');
                    $title   = esc_html($post['title'] ?? '');
                    $excerpt_raw = preg_replace('/[#*`_>\-]+/', '', $post['excerpt'] ?? '');
                    $excerpt     = esc_html(mb_strlen($excerpt_raw) > 120 ? mb_substr($excerpt_raw, 0, 120) . '...' : $excerpt_raw);
                    $image   = esc_url($post['featured_image'] ?? '');
                    $date    = !empty($post['created_at']) ? date('F j, Y', strtotime($post['created_at'])) : '';
                    $url     = esc_url(home_url('/blog-post/') . '?slug=' . $slug);
                ?>
                <article class="blog-card">
                    <div class="blog-card-image">
                        <?php if ($image): ?>
                            <img src="<?= $image ?>" alt="<?= $title ?>">
                        <?php else: ?>
                            <img src="<?php echo get_template_directory_uri(); ?>/imagens/blog/ece298d0ec2c16f10310d45724b276a6035cb503.png" alt="<?= $title ?>">
                        <?php endif; ?>
                    </div>
                    <div class="blog-card-content">
                        <?php if ($date): ?><p class="blog-card-date"><?= $date ?></p><?php endif; ?>
                        <h2 class="blog-card-title"><?= $title ?></h2>
                        <?php if ($excerpt): ?><p class="blog-card-excerpt"><?= $excerpt ?></p><?php endif; ?>
                        <a href="<?= $url ?>" class="blog-card-link">Read More →</a>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>

    </div>
</section>

<?php get_footer(); ?>
