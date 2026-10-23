import 'user.dart';

class Review {
  final int id;
  final int productId;
  final int userId;
  final int rating;
  final String? comment;
  final DateTime createdAt;
  final User? user;

  Review({
    required this.id,
    required this.productId,
    required this.userId,
    required this.rating,
    this.comment,
    required this.createdAt,
    this.user,
  });

  factory Review.fromJson(Map<String, dynamic> json) {
    return Review(
      id: json['id'] as int,
      productId: json['product_id'] as int,
      userId: json['user_id'] as int,
      rating: json['rating'] as int,
      comment: json['comment'] as String?,
      createdAt: DateTime.parse(json['created_at']),
      user: json['user'] != null ? User.fromJson(json['user']) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {'rating': rating, 'comment': comment};
  }
}
