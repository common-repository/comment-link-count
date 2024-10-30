<?php
/**
 * Comment Link Count
 * 
 * Copyright 2010 by hakre <hakre.wordpress.com>, some rights reserved.
 *
 * Wordpress Plugin Header:
 * 
 *   Plugin Name:    Comment Link Count
 *   Plugin URI:     http://hakre.wordpress.com/plugins/comment-link-count/
 *   Description:    Control counting of links in new commments while detecting spam.
 *   Version:        0.1-beta-1
 *   Stable tag:     0.1
 *   Min WP Version: 3.0.0
 *   Author:         hakre
 *   Author URI:     http://hakre.wordpress.com/
 *   Donate link:    http://www.fsfe.org/donate/donate.html
 *   Tags:           Comment, Link, Spam, Count, External Link, Internal Link
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

return CommentLinkCountPlugin::bootstrap();

/**
 * WP 3.0 based implementation.
 */
class CommentLinkCountPlugin {
	/**
	 * number of links
	 * @var int
	 */
	static $linksCount=false;
	static function bootstrap() {
		self::isCommentAddRequest()
			&& self::init()
			;
	}
	static function isCommentAddRequest() {
		return (bool) isset($_POST['comment_post_ID']);
	}
	static function init() {
		add_filter($hook='comment_text'         , array(__CLASS__, $hook), 101, 1);
		add_filter($hook='comment_max_links_url', array(__CLASS__, $hook), 10, 2);
	}
	static function comment_text($comment) {
		static $run=0;
		if ($run++) {
			self::$linksCount=false;
			return $comment;
		}
		($urls = self::extractUrlsFrom($comment))
			&& self::$linksCount = self::countExternal($urls)
			;
		return $comment;
	}
	static function extractUrlsFrom($comment) {
		$html    = stripslashes($comment);
		$matches = array();
		$pattern = '/<a [^>]*href=(?|"([^"]*)"|'."'([^']*)'".')/i';
		$result  = preg_match_all($pattern, $html, $matches );
		return false === $result ? false : $matches[1]; 
	}
	static function countExternal($urls) {
		return count(array_filter($urls, array(__CLASS__,'isExternal')));
	}
	static function isExternal($url) {
		$url = (string) $url;
		if (0===strlen($url)||'#'===$url[0]) return false;
		return true;
	}
	static function comment_max_links_url($num_links, $url) {
		if (false===self::$linksCount)
			return $num_links;
		$num_links = self::$linksCount;
		self::$linksCount = false; 
		return $num_links;
	}
}

#EOF;