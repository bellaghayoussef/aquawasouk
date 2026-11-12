import 'package:flutter/material.dart';

/// A robust, pure-Flutter widget that renders either rich HTML descriptions
/// (with paragraphs, unordered/ordered lists, bold/italic, etc.) or simple plain text.
class HtmlDescriptionView extends StatelessWidget {
  final String htmlOrPlainText;
  final TextStyle? baseStyle;
  final TextStyle? boldStyle;
  final Color bulletColor;
  final double spacing;

  const HtmlDescriptionView({
    super.key,
    required this.htmlOrPlainText,
    this.baseStyle,
    this.boldStyle,
    this.bulletColor = const Color(0xFF0284C7),
    this.spacing = 8.0,
  });

  /// Check if the text contains HTML markup
  static bool hasHtmlTags(String text) {
    if (text.isEmpty) return false;
    final lower = text.toLowerCase();
    return lower.contains('<p') ||
        lower.contains('<ul') ||
        lower.contains('<ol') ||
        lower.contains('<li') ||
        lower.contains('<br') ||
        lower.contains('<div') ||
        lower.contains('<strong') ||
        lower.contains('<b') ||
        lower.contains('<em') ||
        lower.contains('<i') ||
        lower.contains('<span') ||
        lower.contains('&nbsp;') ||
        RegExp(r'<[a-z/][\s\S]*>', caseSensitive: false).hasMatch(text);
  }

  /// Decode common HTML entities
  static String decodeHtmlEntities(String text) {
    return text
        .replaceAll('&nbsp;', ' ')
        .replaceAll('&amp;', '&')
        .replaceAll('&lt;', '<')
        .replaceAll('&gt;', '>')
        .replaceAll('&quot;', '"')
        .replaceAll('&#39;', "'")
        .replaceAll('&apos;', "'")
        .replaceAll('&rsquo;', "'")
        .replaceAll('&lsquo;', "'")
        .replaceAll('&rdquo;', '"')
        .replaceAll('&ldquo;', '"')
        .replaceAll('&bull;', '•')
        .replaceAll('&mdash;', '—')
        .replaceAll('&ndash;', '–')
        .replaceAll('&eacute;', 'é')
        .replaceAll('&egrave;', 'è')
        .replaceAll('&ecirc;', 'ê')
        .replaceAll('&agrave;', 'à')
        .replaceAll('&ugrave;', 'ù')
        .replaceAll('&ocirc;', 'ô')
        .replaceAll('&ccedil;', 'ç');
  }

  @override
  Widget build(BuildContext context) {
    final rawText = htmlOrPlainText.trim();

    if (rawText.isEmpty) {
      return Text(
        'Aucune description disponible pour cet article.',
        style: baseStyle ??
            const TextStyle(
              fontSize: 14,
              color: Color(0xFF64748B),
              height: 1.5,
              fontStyle: FontStyle.italic,
            ),
      );
    }

    final defaultBaseStyle = baseStyle ??
        const TextStyle(
          fontSize: 14,
          color: Color(0xFF334155),
          height: 1.55,
        );

    final defaultBoldStyle = boldStyle ??
        defaultBaseStyle.copyWith(
          fontWeight: FontWeight.w700,
          color: const Color(0xFF0F172A),
        );

    // If simple plain text without any HTML tags
    if (!hasHtmlTags(rawText)) {
      return Text(
        decodeHtmlEntities(rawText),
        style: defaultBaseStyle,
      );
    }

    // Parse and render HTML blocks
    final blocks = _parseHtmlBlocks(rawText);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: blocks.map((block) {
        if (block.isListItem) {
          return Padding(
            padding: EdgeInsets.only(bottom: spacing),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Bullet Point
                Container(
                  margin: const EdgeInsets.only(top: 8, right: 10, left: 2),
                  width: 5.5,
                  height: 5.5,
                  decoration: BoxDecoration(
                    color: bulletColor,
                    shape: BoxShape.circle,
                  ),
                ),
                // Content
                Expanded(
                  child: RichText(
                    text: TextSpan(
                      children: _parseInlineHtml(
                        block.content,
                        defaultBaseStyle,
                        defaultBoldStyle,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          );
        } else {
          // Standard Paragraph
          return Padding(
            padding: EdgeInsets.only(bottom: spacing),
            child: RichText(
              text: TextSpan(
                children: _parseInlineHtml(
                  block.content,
                  defaultBaseStyle,
                  defaultBoldStyle,
                ),
              ),
            ),
          );
        }
      }).toList(),
    );
  }

  /// Break HTML into block units (list items and paragraphs)
  List<_HtmlBlock> _parseHtmlBlocks(String html) {
    final blocks = <_HtmlBlock>[];

    // Normalize breaks
    var cleanHtml = html
        .replaceAll(RegExp(r'<br\s*/?>', caseSensitive: false), '\n')
        .trim();

    // Check if there are <li> tags
    final liRegex = RegExp(r'<li[^>]*>([\s\S]*?)<\/li>', caseSensitive: false);
    final hasListItems = liRegex.hasMatch(cleanHtml);

    if (hasListItems) {
      // Extract any content before the first list
      final firstLiMatch = liRegex.firstMatch(cleanHtml);
      if (firstLiMatch != null && firstLiMatch.start > 0) {
        final preText = cleanHtml.substring(0, firstLiMatch.start);
        final cleanPre = _cleanNonListContent(preText);
        if (cleanPre.isNotEmpty) {
          blocks.add(_HtmlBlock(content: cleanPre, isListItem: false));
        }
      }

      // Extract all list items
      final matches = liRegex.allMatches(cleanHtml);
      for (final match in matches) {
        var liInner = match.group(1)?.trim() ?? '';
        // Remove enclosing <p> if inside <li>
        liInner = liInner.replaceAll(RegExp(r'<\/?p[^>]*>', caseSensitive: false), '');
        liInner = decodeHtmlEntities(liInner).trim();
        if (liInner.isNotEmpty) {
          blocks.add(_HtmlBlock(content: liInner, isListItem: true));
        }
      }

      // Extract any content after the last list
      final lastMatch = matches.last;
      if (lastMatch.end < cleanHtml.length) {
        final postText = cleanHtml.substring(lastMatch.end);
        final cleanPost = _cleanNonListContent(postText);
        if (cleanPost.isNotEmpty) {
          blocks.add(_HtmlBlock(content: cleanPost, isListItem: false));
        }
      }
    } else {
      // No <li> tags, split by <p> or newlines
      final pRegex = RegExp(r'<p[^>]*>([\s\S]*?)<\/p>', caseSensitive: false);
      final pMatches = pRegex.allMatches(cleanHtml);

      if (pMatches.isNotEmpty) {
        for (final m in pMatches) {
          final content = decodeHtmlEntities(m.group(1) ?? '').trim();
          if (content.isNotEmpty) {
            blocks.add(_HtmlBlock(content: content, isListItem: false));
          }
        }
      } else {
        // Fallback: strip outer div/tags and split by newlines
        final stripped = decodeHtmlEntities(
          cleanHtml.replaceAll(RegExp(r'<[^>]*>'), ''),
        ).trim();
        final lines = stripped.split('\n');
        for (final line in lines) {
          final l = line.trim();
          if (l.isNotEmpty) {
            final isBullet = l.startsWith('•') || l.startsWith('-') || l.startsWith('*');
            blocks.add(
              _HtmlBlock(
                content: isBullet ? l.substring(1).trim() : l,
                isListItem: isBullet,
              ),
            );
          }
        }
      }
    }

    return blocks;
  }

  String _cleanNonListContent(String text) {
    return decodeHtmlEntities(
      text
          .replaceAll(RegExp(r'<\/?(ul|ol|div)[^>]*>', caseSensitive: false), '')
          .replaceAll(RegExp(r'<\/?p[^>]*>', caseSensitive: false), '\n')
          .trim(),
    );
  }

  /// Parse inline HTML tags like <strong>, <b>, <em>, <i>
  List<InlineSpan> _parseInlineHtml(
    String text,
    TextStyle baseStyle,
    TextStyle boldStyle,
  ) {
    final spans = <InlineSpan>[];
    final regex = RegExp(
      r'<(strong|b|em|i)[^>]*>([\s\S]*?)<\/\1>',
      caseSensitive: false,
    );

    int lastIndex = 0;
    for (final match in regex.allMatches(text)) {
      // Preceding normal text
      if (match.start > lastIndex) {
        final normalText = text
            .substring(lastIndex, match.start)
            .replaceAll(RegExp(r'<[^>]*>'), '');
        if (normalText.isNotEmpty) {
          spans.add(TextSpan(text: decodeHtmlEntities(normalText), style: baseStyle));
        }
      }

      final tag = match.group(1)?.toLowerCase();
      final content = decodeHtmlEntities(
        (match.group(2) ?? '').replaceAll(RegExp(r'<[^>]*>'), ''),
      );

      if (tag == 'strong' || tag == 'b') {
        spans.add(TextSpan(text: content, style: boldStyle));
      } else if (tag == 'em' || tag == 'i') {
        spans.add(
          TextSpan(
            text: content,
            style: baseStyle.copyWith(fontStyle: FontStyle.italic),
          ),
        );
      } else {
        spans.add(TextSpan(text: content, style: baseStyle));
      }

      lastIndex = match.end;
    }

    // Remaining text
    if (lastIndex < text.length) {
      final remaining = text
          .substring(lastIndex)
          .replaceAll(RegExp(r'<[^>]*>'), '');
      if (remaining.isNotEmpty) {
        spans.add(TextSpan(text: decodeHtmlEntities(remaining), style: baseStyle));
      }
    }

    if (spans.isEmpty) {
      spans.add(TextSpan(
        text: decodeHtmlEntities(text.replaceAll(RegExp(r'<[^>]*>'), '')),
        style: baseStyle,
      ));
    }

    return spans;
  }
}

class _HtmlBlock {
  final String content;
  final bool isListItem;

  _HtmlBlock({required this.content, required this.isListItem});
}

