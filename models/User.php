<?php
declare(strict_types=1);

namespace app\models;

use yii\web\IdentityInterface;

class User extends Benutzer implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id): static|null
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null): static|null
    {
        throw new \yii\base\NotSupportedException('Access tokens werden nicht unterstuetzt.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername(string $username): static|null
    {
        return static::findOne(['email' => $username]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int|string
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey(): string|null
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey): bool
    {
        return true;
    }
}
