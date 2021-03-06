<?php
namespace Avatar;

class Avatars {

	public static function getLinkFor($userId, $res = false) {
		global $wgAvatarPathPrefix;
		if ($res !== false) {
			return $wgAvatarPathPrefix . "$userId/$res.png";
		} else {
			return $wgAvatarPathPrefix . "$userId/128.png";
		}
	}

	public static function normalizeResolution($res) {
		if ($res === 'original') {
			return 'original';
		}
		$res = intval($res);

		global $wgAllowedAvatarRes;
		foreach ($wgAllowedAvatarRes as $r) {
			if ($res <= $r) {
				return $r;
			}
		}

		return 'original';
	}

	public static function getAvatar(\User $user, $res) {
		global $wgDefaultAvatarRes;
		$path = null;

		// If user exists
		if ($user && $user->getId()) {
			global $wgAvatarUploadDirectory;
			$avatarPath = "/{$user->getId()}/$res.png";

			// Check if requested avatar thumbnail exists
			if (file_exists($wgAvatarUploadDirectory . $avatarPath)) {
				$path = $avatarPath;
			} else if ($res !== 'original') {
				// Dynamically generate upon request
				$originalAvatarPath = "/{$user->getId()}/original.png";
				if (file_exists($wgAvatarUploadDirectory . $originalAvatarPath)) {
					$image = Thumbnail::open($wgAvatarUploadDirectory . $originalAvatarPath);
					$image->createThumbnail($res, $wgAvatarUploadDirectory . $avatarPath);
					$image->cleanup();
					$path = $avatarPath;
				}
			}
		}

		return $path;
	}

	public static function hasAvatar(\User $user) {
		global $wgDefaultAvatar;
		return self::getAvatar($user, 'original') !== null;
	}

	public static function deleteAvatar(\User $user) {
		global $wgAvatarUploadDirectory;
		$dirPath = $wgAvatarUploadDirectory . "/{$user->getId()}/";
		if (!is_dir($dirPath)) {
			return false;
		}
		$files = glob($dirPath . '*', GLOB_MARK);
		foreach ($files as $file) {
			unlink($file);
		}
		rmdir($dirPath);
		return true;
	}

}
