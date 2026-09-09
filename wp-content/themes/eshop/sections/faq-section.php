<?php
$faq = carbon_get_post_meta(get_the_ID(), 'faq');

if (!empty($faq)) :
?>
    <section class="faq-section">
        <div class="fixed-container">

            <h2>FAQ</h2>

            <ul class="faq-items__list">

                <?php foreach ($faq as $item) : ?>

                    <li class="faq-items__item">

                        <?php if (!empty($item['question'])) : ?>

                            <button
                                type="button"
                                class="faq-items__question"
                                aria-expanded="false">

                                <span class="faq-items__question__text">
                                    <?= esc_html($item['question']); ?>
                                </span>

                                <span class="faq-items__question__icon"></span>

                            </button>

                        <?php endif; ?>

                        <?php if (!empty($item['answer'])) : ?>

                            <div class="faq-items__answer">
                                <?= apply_filters('the_content', $item['answer']); ?>
                            </div>

                        <?php endif; ?>

                    </li>

                <?php endforeach; ?>

            </ul>
        </div>

    </section>

<?php endif; ?>