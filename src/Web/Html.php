<?php

namespace Xin\Support\Web;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMText;
use DOMXPath;
use LogicException;
use Xin\Support\Str;

/**
 * HTML 处理工具类
 * 提供HTML解析、标签/属性清理、空节点移除、格式压缩等功能
 */
class Html
{
	/**
	 * HTML文档加载标志
	 */
	public const HTML_FLAGS = LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD;

	/** 私有构造，禁止实例化 */
	private function __construct()
	{
	}

	/**
	 * 创建DOMDocument实例并配置
	 * @param string|DOMNode $html
	 * @return DOMDocument
	 */
	public static function document($html, string $encoding = 'UTF-8', int $flags = self::HTML_FLAGS, array $options = [])
	{
		$options = array_merge([
		], $options);

		if ($html instanceof DOMDocument) {
			return $html;
		} elseif ($html instanceof DOMNode) {
			$html = self::toHtmlString($html);
		}

		// // 1. 统一转成 UTF-8（无论原来是 GBK/Windows-1252/ISO-8859-1）
		// $enc = mb_detect_encoding($html, ['UTF-8', 'GBK', 'ISO-8859-1'], true);
		// $html = $enc === 'UTF-8' ? $html : mb_convert_encoding($html, 'UTF-8', $enc);
		//
		// // 2. 整个字节流做 NFC 正规化（组合字符 → 预组合）
		// if (class_exists('Normalizer')) {
		// 	$html = Normalizer::normalize($html, Normalizer::FORM_C);
		// }

		// 强制闭合一些 HTML 标签
		$html = preg_replace('/<source\b([^>]*)>/i', '<source $1/>', $html);
		// 移除 Content-Type
		$html = preg_replace('#<meta\b[^>]*\bhttp-equiv\s*=\s*["\']?Content-Type["\']?[^>]*>#i', '', $html);

		// 创建实例并统一配置
		$document = new DOMDocument();
		$document->preserveWhiteSpace = false; // 去掉纯空白节点
		$document->formatOutput = false; // 保存时无缩进（可后面再改）

		// 屏蔽 libxml 报错，但记得清缓冲区
		@libxml_disable_entity_loader(true);
		libxml_use_internal_errors(true);

		// 解决中文乱码：先拼 XML 头再加载
		$loaded = $document->loadHTML($html, $flags);
		// $loaded = $document->loadHTML('<?xml encoding="UTF-8">' . $html, self::HTML_FLAGS);
		if ($loaded === false) {
			$errs = libxml_get_errors();
			libxml_clear_errors();
			throw new LogicException('libXML load failed: ' . print_r($errs, true));
		}

		// 指定输出编码（保存阶段生效
		$document->encoding = $encoding;

		// 清掉本次累积的错误，防止污染下次调用
		libxml_clear_errors();

		return $document;
	}

	/**
	 * 创建XPath实例并配置
	 * @param DOMNode|DOMXPath $nodeOrXpath
	 * @return DOMXPath
	 */
	public static function xpath($nodeOrXpath)
	{
		if ($nodeOrXpath instanceof DOMXPath) {
			return $nodeOrXpath;
		}

		$nodeOrXpath = $nodeOrXpath instanceof DOMDocument ? $nodeOrXpath : $nodeOrXpath->ownerDocument;
		if (!$nodeOrXpath) {
			throw new LogicException('Detached or invalid DOMNode');
		}

		return new DOMXPath($nodeOrXpath);
	}

	/**
	 * 快速清理HTML
	 * 支持自定义清理规则，适用于大多数场景
	 *
	 * @param string|DOMNode $html 原始HTML内容
	 * @param array $options 清理选项
	 * @return string|DOMNode 清理后的HTML
	 */
	public static function clean($html, array $options = [], $toString = true)
	{
		$node = self::document($html);

		// 合并选项
		$options = array_merge([
			'remove_tags' => [
				'header', 'footer', 'nav', 'aside',
				'svg', 'noscript', 'iframe', 'frame',
				'advertisement', 'ad',
			],
			'allow_attributes' => [
				'id', 'name', 'src', 'href', 'alt',
				'data-*',
			],
			'remove_empty_text_nodes' => true,
			'remove_empty_nodes' => true,
			'remove_comments' => true,
			'remove_meta' => true,
			'remove_styles' => true,
			'remove_scripts' => true,
			'remove_hidden_elements' => true,
			'compress_whitespace' => true,
		], $options);

		// 移除HTML中的meta标签
		if ($options['remove_meta']) {
			self::removeMeta($node);
		}

		// 移除HTML中的样式
		if ($options['remove_styles']) {
			self::removeStyles($node);
		}

		// 移除HTML中的脚本
		if ($options['remove_scripts']) {
			self::removeScripts($node);
		}

		// 移除HTML中的标签
		if ($options['remove_tags']) {
			self::removeTags($node, $options['remove_tags']);
		}

		// 移除隐藏的元素
		if ($options['remove_hidden_elements']) {
			self::removeHiddenElements($node);
		}

		// 移除HTML中的空节点
		if ($options['remove_empty_nodes']) {
			self::removeEmptyNodes($node);
		}

		// 允许的属性
		if ($options['allow_attributes']) {
			self::removeAttributes($node, $options['allow_attributes']);
		}

		// 移除HTML中的注释
		if ($options['remove_comments']) {
			self::removeComments($node);
		}

		return $toString ? self::toHtmlString($node) : $node;
	}

	/**
	 * 移除HTML中的标签
	 * @param DOMNode $root
	 * @param array $removeTags
	 * @return DOMNode
	 */
	public static function removeTags(DOMNode $root, array $removeTags)
	{
		foreach ($removeTags as $tag) {
			// 使用XPath查询所有后代中的目标标签
			$elements = self::findAll($root, ".//{$tag}");
			while ($elements->length) {
				$element = $elements->item(0);
				$element->parentNode->removeChild($element);
			}
		}

		return $root;
	}

	/**
	 * 移除meta和link标签
	 *
	 * @return DOMNode
	 */
	public static function removeMeta(DOMNode $root)
	{
		return self::removeTags($root, ['meta', 'link']);
	}

	/**
	 * 移除样式（style标签和style属性）
	 *
	 * @return DOMNode
	 */
	public static function removeStyles(DOMNode $root)
	{
		return self::removeTags($root, ['style']);
	}

	/**
	 * 移除脚本（script标签和事件属性）
	 *
	 * @return DOMNode
	 */
	public static function removeScripts(DOMNode $root)
	{
		return self::removeTags($root, ['script']);
	}

	/**
	 * 遍历移除隐藏的元素
	 * @param DOMNode $root
	 * @return DOMNode
	 */
	public static function removeHiddenElements(DOMNode $root)
	{
		$xpath = self::xpath($root);

		// 查找所有可能隐藏的元素
		// 1. 通过 style 属性判断
		$hiddenNodes = $xpath->query(".//*[contains(@style, 'display:none') or contains(@style, 'display: none') or contains(@style, 'visibility:hidden') or contains(@style, 'visibility: hidden')]", $root);

		// 移除隐藏元素
		foreach ($hiddenNodes as $hiddenNode) {
			$hiddenNode->parentNode->removeChild($hiddenNode);
		}

		// 2. 通过 hidden 属性判断
		$hiddenAttrNodes = $xpath->query(".//*[@hidden]", $root);
		foreach ($hiddenAttrNodes as $hiddenNode) {
			$hiddenNode->parentNode->removeChild($hiddenNode);
		}

		// 3. 通过 aria-hidden 属性判断
		$ariaHiddenNodes = $xpath->query(".//*[@aria-hidden='true']", $root);
		foreach ($ariaHiddenNodes as $hiddenNode) {
			$hiddenNode->parentNode->removeChild($hiddenNode);
		}

		// 4. 通过 class 属性判断，class 中包含 hidden 或 hide，并是完整单词
		$classHiddenNodes = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' hidden ') or contains(concat(' ', normalize-space(@class), ' '), ' hide ')]");
		/** @var DOMElement $hiddenNode */
		foreach ($classHiddenNodes as $hiddenNode) {
			$hiddenNode->parentNode->removeChild($hiddenNode);
		}

		return $root;
	}

	/**
	 * 移除HTML注释
	 *
	 * @return DOMNode|null
	 */
	public static function removeComments(DOMNode $root)
	{
		$nodes = self::findAll($root, '//comment()');

		// 删除/修改 DOM 节点时，节点列表会实时更新，而 DOMNodeList 是一个活的（live）集合。
		while ($nodes->length) {
			$node = $nodes->item(0);
			$node->parentNode->removeChild($node);
		}

		return $root;
	}

	/**
	 * 递归清理节点属性
	 *
	 * @param DOMNode $root 要清理的节点
	 * @param array|null $allows 允许保留的属性列表
	 * @param array|null $excepts 强制移除的属性列表
	 * @return DOMNode 清理后的节点
	 */
	public static function removeAttributes(DOMNode $root, ?array $allows = null, ?array $excepts = null)
	{
		if (!$root instanceof DOMElement) {
			return $root;
		}

		// 遍历所有属性并判断是否保留
		if ($root->hasAttributes()) {
			for ($i = 0; $i < $root->attributes->length; $i++) {
				$attr = $root->attributes->item($i);
				$attrName = $attr->nodeName;

				// 如果指定了允许的属性列表
				if ($allows !== null && !Str::is($allows, $attrName)) {
					$root->removeAttribute($attrName);
				} // 如果指定了排除的属性列表
				elseif ($excepts !== null && Str::is($excepts, $attrName)) {
					$root->removeAttribute($attrName);
				}
			}
		}

		// 递归处理子节点
		foreach ($root->childNodes as $child) {
			self::removeAttributes($child, $allows, $excepts);
		}

		return $root;
	}

	/**
	 * 递归清理空节点
	 *
	 * @param DOMNode $root 要清理的节点
	 * @return DOMNode 清理后的节点
	 */
	public static function removeEmptyNodes(DOMNode $root)
	{
		// 1. 先递归处理子节点（从后往前避免索引偏移）
		while ($root->childNodes->length) {
			$child = $root->childNodes->item(0);

			if ($child instanceof DOMElement) {
				// 递归清理子节点
				self::removeEmptyNodes($child);

				// 检查是否为自闭合标签
				$isSelfClosing = in_array(strtolower($child->tagName), ['img', 'br', 'hr', 'input', 'meta', 'link']);

				// 如果不是自闭合标签且没有文本内容和子元素，则移除
				if (!$isSelfClosing && trim($child->textContent) === '' && $child->childNodes->length === 0) {
					$child->parentNode->removeChild($child);
				}
			} elseif ($child instanceof DOMText) {
				// 移除空白文本节点
				if (trim($child->nodeValue) === '') {
					$child->parentNode->removeChild($child);
				}
			}
		}

		return $root;
	}

	/**
	 * 获取节点属性
	 * @param DOMElement $node
	 * @return array
	 */
	public static function attributes(DOMNode $node)
	{
		$attributes = [];

		foreach ($node->attributes as $attr) {
			$attributes[$attr->nodeName] = $attr->nodeValue;
		}

		return $attributes;
	}

	/**
	 * 获取节点深度
	 * @param DOMNode $current
	 * @param bool $excludeHtmlBody
	 * @return int
	 */
	public static function depth(DOMNode $current, bool $excludeHtmlBody = true)
	{
		$depth = 0;

		while (($current = $current->parentNode) && !($current instanceof DOMDocument)) {
			if ($excludeHtmlBody && in_array($current->tagName, ['html', 'body'])) {
				break;
			}

			$depth++;
		}

		return $depth;
	}

	/**
	 * 获取节点的父级元素
	 * @param DOMNode $current
	 * @param bool $excludeHtmlBody
	 * @return array
	 */
	public static function parents(DOMNode $current, bool $excludeHtmlBody = true)
	{
		$parents = [];

		/** @var DOMElement $current */
		while (($current = $current->parentNode) && !($current instanceof DOMDocument)) {
			if ($excludeHtmlBody && in_array($current->tagName, ['html', 'body'])) {
				break;
			}

			$parents[] = $current;
		}

		return $parents;
	}

	/**
	 * 获取节点的父级元素标签
	 * @param DOMElement $current
	 * @return array
	 */
	public static function parentTags(DOMElement $current)
	{
		$parents = self::parents($current);
		$parents = array_reverse($parents);
		return array_map(function ($node) {
			// 获取当前节点在同兄弟位置
			$position = self::siblingPosition($node);
			return $node->tagName . "[{$position}]";
		}, $parents);
	}

	/**
	 * 获取节点标签
	 * @param DOMElement $current
	 * @param bool $serialize
	 * @return array|string
	 */
	public static function tags(DOMElement $current, $serialize = false)
	{
		$tags = self::parentTags($current);

		// 获取当前节点在同兄弟位置
		$position = self::siblingPosition($current);
		$tags[] = $current->tagName . "[$position]";

		return $serialize ? implode('/', $tags) : $tags;
	}

	/**
	 * 获取节点最接近的元素
	 * @param DOMElement $current
	 * @param string $selector
	 * @return DOMNode|null
	 */
	public static function closest(DOMElement $current, $selector)
	{
		$selector = self::selectorToXpathExpression($selector);
		while ($current && !($current instanceof DOMDocument)) {
			if (self::match($current, $selector)) {
				return $current;
			}

			$current = $current->parentNode;
		}

		return null;
	}

	/**
	 * 获取兄弟节点位置
	 * @param DOMElement $current
	 * @return int
	 */
	public static function siblingPosition(DOMElement $current)
	{
		$index = 0;

		$siblings = $current->parentNode->childNodes;
		foreach ($siblings as $sibling) {
			if ($sibling === $current) {
				break;
			} elseif ($sibling instanceof DOMElement && $sibling->tagName === $current->tagName) {
				$index++;
			}
		}

		return $index + 1;
	}

	/**
	 * 判断 $child 是否包含在 $ancestor 中
	 * @param DOMNode $ancestor
	 * @param DOMNode $child
	 * @return bool
	 */
	public static function contains(DOMNode $ancestor, DOMNode $child)
	{
		if ($ancestor === $child || $ancestor->isSameNode($child)) {
			return false;
		}

		for ($n = $child; $n !== null; $n = $n->parentNode) {
			if ($n === $ancestor) {
				return true;
			}
		}

		return false;
	}

	/**
	 * 判断节点是否匹配指定的XPath表达式
	 *
	 * @param string $selector XPath表达式
	 * @return bool
	 */
	public static function match(DOMNode $node, $xpathExpression)
	{
		if (!$node instanceof DOMElement) {
			return false;
		}

		// 加 . 限制上下文为当前节点本身
		return self::xpath($node)->evaluate('boolean(.' . $xpathExpression . ')', $node);
	}

	/**
	 * 提节点文本，根据 xpath rules 依次匹配，并返回第一个匹配到的结果
	 * @param DOMNode $root
	 * @param array|string $rules
	 * @return string|null
	 */
	public static function readValue(DOMNode $root, $xpathRules)
	{
		$node = self::find($root, $xpathRules);
		if (!$node) {
			return null;
		}

		if ($node instanceof DOMElement) {
			$value = $node->textContent;
		} else {
			$value = $node->nodeValue;
		}

		return trim($value);
	}

	/**
	 * 提节点文本，根据 xpath rules 获取所有匹配到的结果
	 * @param DOMNode $root
	 * @param array|string $xpathRules
	 * @param callable|null $transform
	 * @return array
	 */
	public static function readValues(DOMNode $root, $xpathRules, ?callable $transform = null)
	{
		$nodes = self::findAll($root, $xpathRules);
		if (!$nodes) {
			return [];
		}

		return array_map($transform ?: [self::class, 'value'], iterator_to_array($nodes));
	}

	/**
	 * 获取节点值
	 * @param mixed $node
	 * @return mixed
	 */
	public static function value($node)
	{
		$value = $node;

		if ($node instanceof DOMNode) {
			if ($node instanceof DOMElement) {
				$value = $node->textContent;
			} else {
				$value = $node->nodeValue;
			}
		}

		return $value;
	}

	/**
	 * 通过XPath查找第一个匹配的节点
	 *
	 * @param string $xpath XPath表达式
	 * @return DOMNode|null
	 */
	public static function find(DOMNode $root, string $xpath)
	{
		$nodes = self::findAll($root, $xpath);

		return $nodes->length > 0 ? $nodes->item(0) : null;
	}

	/**
	 * 通过XPath查找所有匹配的节点
	 *
	 * @param string $xpath XPath表达式
	 * @return DOMNodeList
	 */
	public static function findAll(DOMNode $root, string $xpath)
	{
		return self::xpath($root)->query($xpath, $root);
	}

	/**
	 * 通过CSS选择器查找第一个匹配的节点
	 *
	 * @param string $selector CSS选择器
	 * @return DOMNode|null
	 */
	public static function querySelector(DOMNode $root, $selector)
	{
		$xpathExpression = static::selectorToXpathExpression($selector);

		return self::find($root, $xpathExpression);
	}

	/**
	 * 通过CSS选择器查找所有匹配的节点
	 *
	 * @param string $selector CSS选择器
	 * @return DOMNodeList
	 */
	public static function querySelectorAll(DOMNode $root, $selector)
	{
		$xpathExpression = static::selectorToXpathExpression($selector);

		return self::findAll($root, $xpathExpression);
	}

	/**
	 * CSS选择器转XPath选择器
	 *
	 * @param string $selector CSS选择器
	 * @return string XPath选择器
	 */
	public static function selectorToXpathExpression($selector)
	{
		// 处理空选择器
		if (empty($selector)) {
			return '.';
		}

		// 处理特殊选择器
		if ($selector === '*') {
			return '//*';
		}

		// 分割选择器组（逗号分隔）
		$selectors = explode(',', $selector);
		$xpaths = [];

		foreach ($selectors as $sel) {
			$sel = trim($sel);
			if (empty($sel)) {
				continue;
			}

			// 处理后代选择器（空格分隔）
			$parts = preg_split('/\s+/', $sel);
			$xpath = '';

			foreach ($parts as $part) {
				if (empty($part)) {
					continue;
				}

				// 处理不同的选择器类型
				if (strpos($part, '#') === 0) {
					// ID选择器: #id
					$id = substr($part, 1);
					$xpath .= "//*[@id='{$id}']";
				} elseif (strpos($part, '.') === 0) {
					// 类选择器: .class
					$class = substr($part, 1);
					$xpath .= "//*[contains(concat(' ', @class, ' '), ' {$class} ')]";
				} elseif (preg_match('/^([\w-]+)?\[([\w-]+)(?:([~|^$*]?=)"?([^"\]]*)"?)]?$/', $part, $matches)) {
					// 属性选择器: tag[attr="value"] 或 [attr="value"]
					$tag = isset($matches[1]) && $matches[1] ? $matches[1] : '*';
					$attr = $matches[2];
					$operator = isset($matches[3]) ? $matches[3] : '';
					$value = isset($matches[4]) ? $matches[4] : '';

					switch ($operator) {
						case '':
							$xpath .= "//{$tag}[@{$attr}]";
							break;
						case '=':
							$xpath .= "//{$tag}[@{$attr}='{$value}']";
							break;
						case '~=':
							$xpath .= "//{$tag}[contains(concat(' ', @{$attr}, ' '), ' {$value} ')]";
							break;
						case '|=':
							$xpath .= "//{$tag}[@{$attr}='{$value}' or starts-with(@{$attr}, '{$value}-')]";
							break;
						case '^=':
							$xpath .= "//{$tag}[starts-with(@{$attr}, '{$value}')]";
							break;
						case '$=':
							$xpath .= "//{$tag}[substring(@{$attr}, string-length(@{$attr}) - " . (strlen($value) - 1) . ")='{$value}']";
							break;
						case '*=':
							$xpath .= "//{$tag}[contains(@{$attr}, '{$value}')]";
							break;
						default:
							$xpath .= "//{$tag}[@{$attr}]";
					}
				} else {
					// 标签选择器: tag
					$tag = $part;
					$xpath .= "//{$tag}";
				}
			}

			$xpaths[] = $xpath;
		}

		return implode(' | ', $xpaths);
	}

	/**
	 * 获取 body 节点
	 * @param DOMNode $dom
	 * @return DOMElement
	 */
	public static function body(DOMNode $root)
	{
		if ($root instanceof DOMDocument) {
			$body = self::xpath($root)->query('//body');
			if ($body && $body->length) {
				$root = $body->item(0);
			}
		}

		return $root;
	}

	/**
	 * 获取第一个可迭代的子节点
	 * @param DOMNode $node
	 * @return DOMNode
	 */
	public static function canIteratorFirstChild(DOMNode $node)
	{
		$node = $node instanceof DOMDocument ? $node->documentElement : $node;
		if ($node->nodeName === 'html' && $node->firstElementChild) {
			$node = $node->firstElementChild;
		}

		return $node;
	}

	/**
	 * 遍历节点树
	 *
	 * @param callable $callback 回调函数
	 * @return DOMNode
	 */
	public static function each(DOMNode $root, callable $callback)
	{
		$traverse = function (DOMNode $currentNode, int $currentDepth, int $currentIndex, int $currentSiblingIndex, array $parentInfo) use (&$traverse, $callback) {
			$tempNode = $callback($currentNode, $currentDepth, $currentIndex, $currentSiblingIndex, $parentInfo);
			if ($tempNode) {
				$currentNode = $tempNode;
			}

			// 节点没有子节点
			if (!$currentNode->hasChildNodes()) {
				return;
			}


			// 递归遍历子节点
			$index = 0;
			$siblingIndexes = [];

			/** @var DOMNode $childNode */
			for ($i = 0; $i < $currentNode->childNodes->count(); $i++) {
				$childNode = $currentNode->childNodes->item($i);
				if (!($childNode instanceof DOMElement)) {
					continue;
				}

				// 记录当前层级的兄弟节点索引
				$tagName = $childNode->tagName;
				if (!isset($siblingIndexes[$tagName])) {
					$siblingIndexes[$tagName] = 0;
				}
				$siblingIndex = $siblingIndexes[$tagName];

				// 递归遍历子节点
				$traverse($childNode, $currentDepth + 1, $index, $siblingIndex, [
					'depth' => $currentDepth,
					'index' => $currentIndex,
					'sibling_index' => $currentSiblingIndex,
				]);

				// 索引加1
				$index++;
				$siblingIndexes[$tagName]++;
			}
		};

		$traverse(self::canIteratorFirstChild($root), 0, 1, 1, [
			'depth' => 0,
			'index' => 0,
			'sibling_index' => 0,
		]);

		return $root;
	}

	/**
	 * 映射节点树
	 *
	 * @param callable $callback 回调函数
	 * @return array
	 */
	public static function map(DOMNode $root, callable $callback)
	{
		$traverse = function (DOMNode $currentNode, int $currentDepth, int $currentIndex, int $currentSiblingIndex, array $parentInfo) use (&$traverse, $callback) {
			$resultItem = $callback($currentNode, $currentDepth, $currentIndex, $currentSiblingIndex, $parentInfo);
			if (!$currentNode->hasChildNodes()) {
				return $resultItem;
			}

			// 递归遍历子节点
			$index = 0;
			$siblingIndexes = [];

			/** @var DOMNode $childNode */
			for ($i = 0; $i < $currentNode->childNodes->count(); $i++) {
				$childNode = $currentNode->childNodes->item($i);

				if ($childNode->nodeType === XML_ELEMENT_NODE) {
					// 记录当前层级的兄弟节点索引
					$tagName = $childNode->tagName;
					if (!isset($siblingIndexes[$tagName])) {
						$siblingIndexes[$tagName] = 0;
					}
					$siblingIndex = $siblingIndexes[$tagName];

					// 递归遍历子节点
					$child = $traverse($childNode, $currentDepth + 1, $index, $siblingIndex, [
						'depth' => $currentDepth,
						'index' => $currentIndex,
						'sibling_index' => $currentSiblingIndex,
					]);

					// 索引加1
					$index++;
					$siblingIndexes[$tagName]++;
				} else {
					// 递归遍历子节点
					$child = $traverse($childNode, $currentDepth + 1, -1, -1, [
						'depth' => $currentDepth,
						'index' => $currentIndex,
						'sibling_index' => $currentSiblingIndex,
					]);
				}

				if (!empty($child)) {
					$resultItem['child'][] = $child;
				}
			}

			return $resultItem;
		};

		return $traverse(self::canIteratorFirstChild($root), 0, 1, 1, [
			'depth' => 0,
			'index' => 0,
			'sibling_index' => 0,
		]);
	}

	/**
	 * 获取 DOM 节点的数组结构
	 * @param DOMNode $node
	 * @return array
	 */
	public static function toArray(DOMNode $node)
	{
		return self::map($node, function (DOMNode $node, $level) {
			$item = [
				'tag' => $node->nodeName,
				'level' => $level,
			];

			if ($node->hasAttributes()) {
				$item['attrs'] = $node->attributes ? self::attributes($node) : [];
			}

			if ($node->nodeType == XML_TEXT_NODE) {
				$item['text'] = trim($node->nodeValue);
			}

			return $item;
		});
	}

	/**
	 * 补全相对链接
	 *
	 * @param DOMNode $root 要处理的节点
	 * @param string $baseUrl 基础URL
	 * @return DOMNode 处理后的节点
	 */
	public static function completeRelativeLinks(DOMNode $root, string $baseUrl)
	{
		// 如果是元素节点，检查是否有需要补全的链接属性
		if ($root instanceof DOMElement) {
			// 处理 href 属性
			if ($root->hasAttribute('href')) {
				$href = $root->getAttribute('href');
				if (!preg_match('/^(https?:)?\/\//i', $href)) {
					$root->setAttribute('href', rtrim($baseUrl, '/') . '/' . ltrim($href, '/'));
				}
			}

			// 处理 src 属性
			if ($root->hasAttribute('src')) {
				$src = $root->getAttribute('src');
				if (!preg_match('/^(https?:)?\/\//i', $src)) {
					$root->setAttribute('src', rtrim($baseUrl, '/') . '/' . ltrim($src, '/'));
				}
			}
		}

		// 递归处理所有子节点
		foreach ($root->childNodes as $childNode) {
			static::completeRelativeLinks($childNode, $baseUrl);
		}

		return $root;
	}

	/**
	 * 获取节点内容
	 * @param DOMNode|null $root
	 * @param bool $compress
	 * @return string
	 */
	public static function toHtmlString(?DOMNode $root = null, bool $compress = true)
	{
		if ($root === null) {
			return '';
		}

		// 获取节点内容
		$document = $root instanceof DOMDocument ? $root : $root->ownerDocument;

		$html = $document->saveHTML($root);
		// 把 saveHTML 产生的 HTML 实体还原成真实 Unicode
		$html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

		// 移除 <?xml encoding="UTF-8" ...
		if (Str::startsWith($html, '<?xml encoding="UTF-8">')) {
			$html = substr($html, 23);
		}

		// 压缩空白 / 格式化
		return $compress ? self::compressWhitespace($html) : self::beautify($html);
	}

	/**
	 * 美化 HTML
	 * @param string $html
	 * @return string
	 */
	public static function beautify(string $html): string
	{
		// 拆成 token 流：标签开/闭、文本、注释（保留原正则）
		$tokens = preg_split('#(<!--.*?-->|<[^>]+>)#s', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		$indentSize = 1;
		$indentChar = "\t";
		$depth = 0;
		$output = '';
		// 原内联标签列表不变 + 正则加i忽略大小写
		$inlineTags = implode('|', [
			'a', 'abbr', 'acronym', 'b', 'bdo', 'big', 'br', 'button', 'cite', 'code', 'dfn', 'em', 'i', 'img',
			'kbd', 'label', 'map', 'object', 'q', 'samp', 'script', 'select', 'small', 'strong',
			'sub', 'sup', 'textarea', 'input', 'time', 'tt', 'var',
		]);

		// 修复1：加i忽略大小写 + 匹配闭合标签（原逻辑不变，仅补全匹配规则）
		$isInlineTag = function ($token) use ($inlineTags) {
			return preg_match('/^<\/?(' . $inlineTags . ')\b/i', $token);
		};

		// 修复2：新增自闭合标签判断（兼容HTML5无/和有/写法，原逻辑补充）
		$isSelfClosing = function ($token) {
			$selfTags = [
				'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta',
				'param', 'source', 'track', 'wbr',
			];
			$tagName = preg_replace('/^<([a-z0-9]+)\b.*$/i', '$1', $token);
			return in_array(strtolower($tagName), $selfTags) || preg_match('/\/>$/', $token);
		};

		foreach ($tokens as $token) {
			// 修复3：仅判断空token，不全局trim（保留标签/文本内的原始空白）
			$trimToken = trim($token);
			if ($trimToken === '') {
				continue;
			}

			// 注释：修复4：保留原始注释（不trim），单独缩进
			if (str_starts_with($trimToken, '<!--')) {
				$output .= str_repeat($indentChar, $depth * $indentSize) . rtrim($token) . "\n";
				continue;
			}

			// 闭合标签 </...> （原逻辑不变 + 防止depth为负）
			if (str_starts_with($trimToken, '</')) {
				if (!$isInlineTag($trimToken)) {
					$depth = max($depth - 1, 0); // 修复：防止层级为负
				}
				$output .= str_repeat($indentChar, $depth * $indentSize) . $trimToken . "\n";
				continue;
			}

			// 开标签 <...> （原逻辑 + 自闭合标签不增加depth）
			if (str_starts_with($trimToken, '<')) {
				$output .= str_repeat($indentChar, $depth * $indentSize) . $trimToken . "\n";
				// 修复：自闭合标签/内联标签 都不增加层级
				if (!$isSelfClosing($trimToken) && !$isInlineTag($trimToken)) {
					$depth++;
				}
				continue;
			}

			// 文本节点：修复5：彻底删掉htmlspecialchars（原核心错误）+ 仅合并多余空白
			$text = preg_replace('/\s+/', ' ', $token);
			if ($text !== '') {
				$output .= str_repeat($indentChar, $depth * $indentSize) . $text . "\n";
			}
		}

		// 去掉最后一行多余换行（原逻辑不变）
		return rtrim($output, "\n");
	}

	/**
	 * 空格缩进替换成指定宽度
	 * @param string $html
	 * @param int $indentSize
	 * @param string $indentChar
	 * @return string
	 */
	public static function reindent(string $html, int $indentSize, string $indentChar = ' ')
	{
		if ($indentSize < 1) {
			return preg_replace('/>\s+</', '><', $html);
		}

		$spaces = str_repeat($indentChar, $indentSize);
		return preg_replace_callback('/^(\s+)/m', function ($m) use ($spaces) {
			$level = (int)(strlen($m[1]) / 2);
			return str_repeat($spaces, $level);
		}, $html);
	}

	/**
	 * 压缩HTML空白字符（优化页面加载性能）
	 *
	 * @param string $html 原始HTML内容
	 * @return string 压缩后的HTML
	 */
	public static function compressWhitespace(string $html)
	{
		// 1. 压缩多个空白符为单个空格
		$html = preg_replace('/\s+/u', ' ', $html);

		// 2. 移除标签前后的空格
		$html = preg_replace('/>\s+</u', '><', $html);

		// 3. 移除开头和结尾的空白符
		return trim($html);
	}
}
