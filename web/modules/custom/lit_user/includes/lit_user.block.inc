<?php

/**
 * @file
 * Integration with block system.
 */
use Drupal\Core\Url;

/**
 * Implements hook_block_view_BASE_BLOCK_ID_alter().
 */
function lit_user_block_view_user_login_block_alter (array &$build , \Drupal\Core\Block\BlockPluginInterface $block)
{
    $build['#pre_render'][] = 'lit_user_user_login_block_prerender';
}

/**
 * Prerender function for user login block.
 */
function lit_user_user_login_block_prerender (array $build)
{
    if (\Drupal::config('user.settings')->get('register') != USER_REGISTER_ADMINISTRATORS_ONLY) {
        // Change title of the link.
        $build['content']['user_links']['#items']['create_account']['#title'] = t('new user? Create free account');

        // Add Facebook link.
        $build['content']['user_links']['#items']['facebook_login'] = [
            '#type' => 'link' ,
            '#title' => t('Facebook login') ,
            '#url' => Url::fromRoute('simple_fb_connect.redirect_to_fb' , [] , [
                'attributes' => [
                    'title' => t('Facebook login.') ,
                    'class' => ['facebook-login'] ,
                ] ,
            ]) ,
        ];
    }
    return $build;
}
