import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../services/vendor_service.dart';
import '../../config/theme.dart';
import '../../config/api_config.dart';

class VendorSettingsScreen extends StatefulWidget {
  const VendorSettingsScreen({super.key});

  @override
  State<VendorSettingsScreen> createState() => _VendorSettingsScreenState();
}

class _VendorSettingsScreenState extends State<VendorSettingsScreen> {
  final _formKey = GlobalKey<FormState>();
  bool _isLoading = true;
  bool _isSaving = false;

  // Controllers
  final _shopNameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  final _cityController = TextEditingController();
  final _countryController = TextEditingController();
  final _postalCodeController = TextEditingController();
  final _shippingPriceController = TextEditingController();
  final _descriptionController = TextEditingController();

  // Images
  final ImagePicker _picker = ImagePicker();
  String? _currentLogo;
  String? _currentBanner;
  File? _newLogo;
  File? _newBanner;

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  @override
  void dispose() {
    _shopNameController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _cityController.dispose();
    _countryController.dispose();
    _postalCodeController.dispose();
    _shippingPriceController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  Future<void> _loadProfile() async {
    setState(() => _isLoading = true);
    try {
      final profile = await VendorService.getProfile();
      _shopNameController.text = profile['shop_name'] ?? '';
      _phoneController.text = profile['phone'] ?? '';
      _addressController.text = profile['address'] ?? '';
      _cityController.text = profile['city'] ?? '';
      _countryController.text = profile['country'] ?? '';
      _postalCodeController.text = profile['postal_code'] ?? '';
      _shippingPriceController.text =
          profile['shipping_price']?.toString() ?? '0.00';
      _descriptionController.text = profile['description'] ?? '';

      _currentLogo = profile['logo'];
      _currentBanner = profile['banner'];
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading profile: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _pickImage(bool isLogo) async {
    final XFile? image = await _picker.pickImage(source: ImageSource.gallery);
    if (image != null) {
      setState(() {
        if (isLogo) {
          _newLogo = File(image.path);
        } else {
          _newBanner = File(image.path);
        }
      });
    }
  }

  Future<void> _saveProfile() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSaving = true);
    try {
      // 1. Update text data
      final data = {
        'shop_name': _shopNameController.text.trim(),
        'phone': _phoneController.text.trim(),
        'address': _addressController.text.trim(),
        'city': _cityController.text.trim(),
        'country': _countryController.text.trim(),
        'postal_code': _postalCodeController.text.trim(),
        'shipping_price': _shippingPriceController.text.trim(),
        'description': _descriptionController.text.trim(),
      };

      await VendorService.updateProfile(data);

      // 2. Update images if changed
      if (_newLogo != null) {
        await VendorService.updateLogo(_newLogo!);
      }
      if (_newBanner != null) {
        await VendorService.updateBanner(_newBanner!);
      }

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Profile updated successfully'),
            backgroundColor: AppTheme.successColor,
          ),
        );
        Navigator.pop(
            context); // Go back to dashboard to refresh data if needed
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error saving profile: $e'),
            backgroundColor: AppTheme.errorColor,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Store Settings'),
        actions: [
          IconButton(
            icon: const Icon(Icons.check),
            onPressed: _isSaving ? null : _saveProfile,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildSectionHeader('Store Branding'),
                    const SizedBox(height: 16),
                    _buildImageSection(),
                    const SizedBox(height: 24),
                    _buildSectionHeader('Store Information'),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _shopNameController,
                      decoration: const InputDecoration(labelText: 'Shop Name'),
                      validator: (v) => v?.isEmpty == true ? 'Required' : null,
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _descriptionController,
                      decoration:
                          const InputDecoration(labelText: 'Description'),
                      maxLines: 3,
                    ),
                    const SizedBox(height: 24),
                    _buildSectionHeader('Contact & Address'),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _phoneController,
                      decoration: const InputDecoration(labelText: 'Phone'),
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _addressController,
                      decoration: const InputDecoration(labelText: 'Address'),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: TextFormField(
                            controller: _cityController,
                            decoration:
                                const InputDecoration(labelText: 'City'),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: TextFormField(
                            controller: _postalCodeController,
                            decoration:
                                const InputDecoration(labelText: 'Postal Code'),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _countryController,
                      decoration: const InputDecoration(labelText: 'Country'),
                    ),
                    const SizedBox(height: 24),
                    _buildSectionHeader('Shipping'),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _shippingPriceController,
                      keyboardType:
                          const TextInputType.numberWithOptions(decimal: true),
                      decoration: const InputDecoration(
                        labelText: 'Default Shipping Price',
                        prefixText: 'TND ',
                      ),
                      validator: (v) {
                        if (v == null || v.isEmpty) return null;
                        if (double.tryParse(v) == null) return 'Invalid number';
                        return null;
                      },
                    ),
                    const SizedBox(height: 48),
                    SizedBox(
                      width: double.infinity,
                      height: 50,
                      child: ElevatedButton(
                        onPressed: _isSaving ? null : _saveProfile,
                        child: _isSaving
                            ? const CircularProgressIndicator(
                                color: Colors.white)
                            : const Text('Save Changes'),
                      ),
                    ),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Text(
      title,
      style: Theme.of(context).textTheme.titleLarge?.copyWith(
            fontWeight: FontWeight.bold,
            color: AppTheme.primaryColor,
          ),
    );
  }

  Widget _buildImageSection() {
    return Column(
      children: [
        // Banner
        GestureDetector(
          onTap: () => _pickImage(false),
          child: Container(
            height: 150,
            width: double.infinity,
            decoration: BoxDecoration(
              color: Colors.grey[200],
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.grey[300]!),
              image: _newBanner != null
                  ? DecorationImage(
                      image: FileImage(_newBanner!),
                      fit: BoxFit.cover,
                    )
                  : (_currentBanner != null
                      ? DecorationImage(
                          image: NetworkImage(
                              ApiConfig.getImageUrl(_currentBanner!)),
                          fit: BoxFit.cover,
                        )
                      : null),
            ),
            child: _newBanner == null && _currentBanner == null
                ? const Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.add_photo_alternate,
                          size: 40, color: Colors.grey),
                      SizedBox(height: 4),
                      Text('Add Banner Image',
                          style: TextStyle(color: Colors.grey)),
                    ],
                  )
                : Container(
                    alignment: Alignment.bottomRight,
                    padding: const EdgeInsets.all(8),
                    child: Container(
                      padding: const EdgeInsets.all(4),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.8),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.edit, size: 20),
                    ),
                  ),
          ),
        ),
        const SizedBox(height: 16),
        // Logo
        GestureDetector(
          onTap: () => _pickImage(true),
          child: Row(
            children: [
              Container(
                height: 80,
                width: 80,
                decoration: BoxDecoration(
                  color: Colors.grey[200],
                  borderRadius: BorderRadius.circular(40),
                  border: Border.all(color: Colors.grey[300]!),
                  image: _newLogo != null
                      ? DecorationImage(
                          image: FileImage(_newLogo!),
                          fit: BoxFit.cover,
                        )
                      : (_currentLogo != null
                          ? DecorationImage(
                              image: NetworkImage(
                                  ApiConfig.getImageUrl(_currentLogo!)),
                              fit: BoxFit.cover,
                            )
                          : null),
                ),
                child: _newLogo == null && _currentLogo == null
                    ? const Icon(Icons.person, size: 40, color: Colors.grey)
                    : null,
              ),
              const SizedBox(width: 16),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Store Logo',
                      style:
                          TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                    ),
                    Text(
                      'Tap to change',
                      style: TextStyle(color: Colors.grey, fontSize: 12),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
