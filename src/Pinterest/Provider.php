<?php

namespace SocialiteProviders\Pinterest;

use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'PINTEREST';

    /**
     * {@inheritdoc}
     */
    protected $scopes = ['read_public'];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            'https://api.pinterest.com/oauth/',
            $state
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://api.pinterest.com/v1/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            'https://api.pinterest.com/v1/me/',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                ],
                
                'query' => [
                    'fields' => implode(',', ['id', 'first_name', 'last_name', 'url', 'image[small]'])
                ],
            ]
        );

        $contents = $response->getBody()->getContents();

        return json_decode($contents, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        preg_match('#https://www.pinterest.com/(.+?)/#', $user['data']['url'], $matches);
        $nickname = $matches[1];

        return (new User())->setRaw($user)->map(
            [
                'id'       => $user['data']['id'],
                'nickname' => $nickname,
                'name'     => $user['data']['first_name'].' '.$user['data']['last_name'],
                'avatar'   => $user['data']['image']['small']['url'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_merge(
            parent::getTokenFields($code),
            [
                'grant_type' => 'authorization_code',
            ]
        );
    }
}
