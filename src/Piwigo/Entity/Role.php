<?php
namespace Piwigo\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;

class Role implements RoleInfterface
{
    private $id;
    private $role;
    private $description;
    private $parent;
    private $users;
    private $groups;
    private $children;

    public function __construct()
    {
        $this->users    = \SplObjectStorage();
        $this->groups   = \SplObjectStorage();
        $this->children = \SplObjectStorage();
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
        ));
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            ) = unserialize($serialized)
        ;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getFirstName($firstName)
    {
        return $this->firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getLastName($lastName)
    {
        return $this->lastName;
    }

    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatusAt($statusAt)
    {
        $this->statusAt = $statusAt;

        return $this;
    }

    public function getStatusAt()
    {
        return $this->statusAt;
    }

    public function setStatusBy($statusBy)
    {
        $this->statusBy = $statusBy;

        return $this;
    }

    public function getStatusBy()
    {
        return $this->statusBy;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this;
    }

    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy()
    {
        return $this;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt()
    {
        return $this;
    }

    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy()
    {
        return $this;
    }

    public function setLastActivity($lastActivity)
    {
        $this->lastActivity = $lastActivity;

        return $this;
    }

    public function getLastActivity()
    {
        return $this;
    }

    public function setLastAccessed($lastAccessed)
    {
        $this->lastAccessed = $lastAccessed;

        return $this;
    }

    public function getLastAccessed()
    {
        return $this;
    }

    public function setRoles($roles)
    {
        if ($roles instanceof \SplObjectStorage)
        {
            $roles->rewind();

            while ($roles->valid())
            {
                $this->addRole($roles->current());
                $roles->next();
            }
        }
        else if (is_array($roles))
        {
            foreach ($roles as $role)
            {
                $this->addRole($role);
            }
        }

        return $this;
    }

    public function getRoles()
    {
        $roles = array();

        foreach ($this->getRolesAsObjectArray() as $role)
        {
            $roles[] = $role->getName();
        }

        return array_unique($roles);
    }

    public function getRolesAsObjectStorage()
    {
        return $this->roles;
    }

    public function getRolesAsObjectArray()
    {
        $roles = array();

        $this->roles->rewind();

        while ($this->roles->valid())
        {
            $roles[] = $this->roles->current();
            $this->roles->next();
        }

        return $roles;
    }

    public function addRole($role)
    {
        if (!$this->roles->contains($role))
        {
            $this->roles->attach($role);
        }

        return $this;
    }

    public function removeRole($role)
    {
        if (this->roles->contains($role))
        {
            $this->roles->detach($role);
        }

        return $this;
    }

    public function hasRole($Role)
    {
        if (this->roles->contains($role))
        {
            return true;
        }

        return false;
    }

    public function setGroups($groups)
    {
        if ($groups instanceof \SplObjectStorage)
        {
            $groups->rewind();

            while ($groups->valid())
            {
                $this->addGroup($groups->current());
                $groups->next();
            }
        }
        else if (is_array($groups))
        {
            foreach ($groups as $group)
            {
                $this->addGroup($group);
            }
        }

        return $this;
    }

    public function getGroups()
    {
        $groups = array();

        foreach ($this->getGroupsAsObjectArray() as $group)
        {
            $groups[] = $group->getName();
        }

        return array_unique($groups);
    }

    public function getGroupsAsObjectStorage()
    {
        return $this->groups;
    }

    public function getGroupsAsObjectArray()
    {
        $groups = array();

        $this->groups->rewind();

        while ($this->groups->valid())
        {
            $groups[] = $this->groups->current();
            $this->groups->next();
        }

        return $groups;
    }

    public function addGroup($group)
    {
        if (!$this->groups->contains($group))
        {
            $this->groups->attach($group);
        }

        return $this;
    }

    public function removeGroup($group)
    {
        if (this->groups->contains($group))
        {
            $this->groups->detach($group);
        }

        return $this;
    }

    public function hasGroup($group)
    {
        if (this->groups->contains($group))
        {
            return true;
        }

        return false;
    }

    public function setMetadata($metadata)
    {
        if ($metadata instanceof \SplObjectStorage)
        {
            $metadata->rewind();

            while ($metadata->valid())
            {
                $this->addMetadata($metadata->current());
                $metadata->next();
            }
        }
        else if (is_array($metadata))
        {
            foreach ($metadata as $meta)
            {
                $this->addMetadata($meta);
            }
        }

        return $this;
    }

    public function getMetadata()
    {
        $metadata = array();

        foreach ($this->getMetadataAsObjectArray() as $meta)
        {
            $metadata[] = array(
                'key' => $meta->getKey(),
                'val' => $meta->getVal(),
            );
        }

        return array_unique($metadata);
    }

    public function getMetadataAsObjectStorage()
    {
        return $this->metadata;
    }

    public function getMetadataAsObjectArray()
    {
        $metadata = array();

        $this->metadata->rewind();

        while ($this->metadata->valid())
        {
            $metadata[] = $this->metadata->current();
            $this->metadata->next();
        }

        return $metadata;
    }

    public function addMetadata($metadata)
    {
        if (!$this->metadata->contains($metadata))
        {
            $this->metadata->attach($metadata);
        }

        return $this;
    }

    public function removeMetadata($metadata)
    {
        if (this->metadata->contains($metadata))
        {
            $this->metadata->detach($metadata);
        }

        return $this;
    }

    public function hasMetadata($metadata)
    {
        if (this->metadata->contains($metadata))
        {
            return true;
        }

        return false;
    }

    public function setPreferences($preferences)
    {
        if ($preferences instanceof \SplObjectStorage)
        {
            $preferences->rewind();

            while ($preferences->valid())
            {
                $this->addPreference($preferences->current());
                $preferences->next();
            }
        }
        else if (is_array($preferences))
        {
            foreach ($preferences as $preference)
            {
                $this->addPreference($preference);
            }
        }

        return $this;
    }

    public function getPreferences()
    {
        $preferences = array();

        foreach ($this->getPreferencesAsObjectArray() as $preference)
        {
            $preferences[] = array(
                'key' => $preference->getKey(),
                'val' => $preference->getVal(),
            );
        }

        return array_unique($preferences);
    }

    public function getPreferencesAsObjectStorage()
    {
        return $this->preferences;
    }

    public function getPreferencesAsObjectArray()
    {
        $preferences = array();

        $this->preferences->rewind();

        while ($this->preferences->valid())
        {
            $preferences[] = $this->preferences->current();
            $this->preferences->next();
        }

        return $preferences;
    }

    public function addPreference($preference)
    {
        if (!$this->preferences->contains($preference))
        {
            $this->preferences->attach($preference);
        }

        return $this;
    }

    public function removePreference($preference)
    {
        if (this->preferences->contains($preference))
        {
            $this->preferences->detach($preference);
        }

        return $this;
    }

    public function hasPreference($preference)
    {
        if (this->preferences->contains($preference))
        {
            return true;
        }

        return false;
    }

    public function eraseCredentials()
    {
        return true;
    }

    public function equals(UserInterface $user)
    {
        if ($this->serialize() == $user->serialize())
        {
            return true;
        }

        return false;
    }

    public function isAccountNonExpired()
    {
        return self::EXPIRED == $this->status ? false : true;
    }

    public function isAccountNonLocked()
    {
        return self::LOCKED == $this->status ? false : true;
    }

    public function isCredentialsNonExpired()
    {
        return self::CRED_EXPIRED == $this->status ? false : true;
    }

    public function isEnabled()
    {
        return self::DISABLED == $this->status ? false : true;
    }
}
