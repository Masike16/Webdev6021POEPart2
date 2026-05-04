# Pastimes Second-Hand Fashion Store

A website for buying and selling second-hand clothing, built as for the Web Development POE assignment creayed by students ST10451774(Acazia) & ST10452404(Masike).

## The Project Overview

Pastimes is a modern e-commerce platform inspired by the websites we did research on (Vinted and Depop), the websites is designed to facilitate the buying and selling of pre-loved fashion items. The system features role-based access control, secure transactions, and a user-friendly interface.

## Features of the website

### User Management
- **Registration System**: Secure user registration with email validation
- **Role-Based Access**: Buyer, Seller, and Admin roles with appropriate permissions based on their roles
- **Admin Verification**: Admin must verify new users (sellers and buyers) before they can access the system
- **Session Security**: Secure session management with regeneration

### Shopping Features
- **Product Browsing**: Advanced search and filtering capabilities
- **Product Details**: Detailed product views with seller information
- **Database-Based Cart**: User-isolated shopping cart system
- **Checkout Process**: Complete order processing with confirmation
- **Order Management**: Order history and tracking

### Seller Features
- **Product Management**: Add, edit, and delete product listings
- **Image Upload**: Secure file upload with validation
- **Sales Tracking**: Revenue statistics and inventory management
- **Order Management**: Ability to View and manage incoming orders

### Admin Features
- **User Management**: Verify, delete, and manage users
- **Product Oversight**: Monitor and manage all listings
- **Dashboard Analytics**: Comprehensive statistics and metrics
- **System Control**: Full administrative control

### Communication
- **Messaging System**: Direct communication between buyers and sellers
- **Read/Unread Status**: Message tracking and notifications
- **User Profiles**: Comprehensive user information and activity

## 🛠️ Technical Stack

- **Backend**: PHP 8.0+ with MySQLi
- **Database**: MySQL with secure prepared statements
- **Frontend**: HTML5, CSS3, JavaScript
- **Security**: Password hashing, SQL injection prevention, session security
- **Design**: Responsive, mobile-first design with modern UI/UX

## Project Structure

```
Webdev6021POEPart2-main/
├── 📄 index.php                 # Homepage with featured products
├── 📄 register.php              # User registration form
├── 📄 login.php                 # User login
├── 📄 admin_login.php           # Admin login
├── 📄 logout.php                # Session termination
├── 📄 admin_dashboard.php       # Admin control panel
├── 📄 seller_dashboard.php      # Seller management panel
├── 📄 products.php              # Product browsing with search
├── 📄 product_details.php       # Individual product view
├── 📄 cart.php                  # Shopping cart
├── 📄 checkout.php              # Checkout process
├── 📄 order_confirmation.php     # Order receipt
├── 📄 profile.php               # User profiles and messaging
├── 📄 add_product.php           # Add new product (sellers)
├── 📄 edit_product.php          # Edit existing product
├── 📄 add_to_cart.php           # Cart handler
├── 📄 DBConn.php                # Database connection class
├── 📄 loadClothingStore.php     # Database setup script
├── 📁 css/
│   └── 📄 style.css              # Complete responsive styling
├── 📁 js/
│   └── 📄 script.js              # Form validation and interactions
├── 📁 images/                   # Product images
├── 📁 uploads/                  # User upload directory
├── 📄 README.md                 # This file
├── 📄 diagnostic.php            # Database diagnostic tool
├── 📄 test_db.php               # Database connection test
└── 📄 myClothingStore.sql      # Complete SQL export
```

## 🗄️ Database Schema

### Tables Structure

- **tblUser**: User accounts with roles and verification status
- **tblClothes**: Product listings with seller information
- **tblOrders**: Order management and tracking
- **tblOrderItems**: Individual order line items
- **tblMessages**: User-to-user messaging system
- **tblCart**: Database-based shopping cart

## 🚀 Setup Instructions

### Prerequisites for operating the website
- PHP 8.0 or higher
- MySQL 5.7 or higher
- WAMP/XAMP/MAMP server environment
- Web browser with JavaScript enabled

### Installation Steps

1. **Clone/Download the Project**
   ```bash
<<<<<<< HEAD
   # Download from GitHub: Webdev6021POEPart2
   # Extract to your web server directory
   # For WAMP: C:\wamp64\www\Webdev6021POEPart2-main\
   ```

2. **Database Setup**
   - Open your browser and navigate to: `http://localhost/Webdev6021POEPart2-main/loadClothingStore.php`
   - This will automatically create the database and populate it with sample data
=======
   # Extract the folder to your web server directory
   # For WAMP: C:\wamp64\www\WebdevpastimesV4\
   ```

2. **Database Setup**
   - Open your browser and navigate to: `http://localhost/WebdevpastimesV4/loadClothingStore.php`
   - This will automatically create the database and populate it with the sample data
>>>>>>> 434bae7f8eefc794b48669b2e852c68d066d80b8

3. **Verify Installation**
   - Navigate to: `http://localhost/Webdev6021POEPart2-main/`
   - You should see the Pastimes homepage

### Default Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | Admin123! |
| Seller | johnseller | Seller123! |
| Buyer | janebuyer | Buyer123! |

## 🎨 Design Features

### Color Scheme
- **Primary**: #2C3E50 (Deep charcoal)
- **Secondary**: #E74C3C (Coral red)
- **Accent**: #3498DB (Sky blue)
- **Success**: #27AE60 (Mint green)
- **Background**: #F9F9F9 (Light gray)

### Responsive Design
- **Mobile**: 320px - 767px
- **Tablet**: 768px - 1023px
- **Desktop**: 1024px+

### UI Components
- Modern card-based layouts
- Smooth hover effects and transitions
- Professional form styling
- Interactive elements with feedback

## Security Features

- **SQL Injection Prevention**: All queries use prepared statements
- **Password Security**: Hashing with PHP's password_hash() function
- **Session Security**: Session regeneration and secure handling
- **Input Validation**: Server-side validation for all forms
- **File Upload Security**: Type and size validation for images
- **Role-Based Access**: Proper permission checking throughout

## References

This project incorporates design patterns and features inspired by:

- **Vinted (2023)** - UI/UX design inspiration and layout patterns
- **Depop (2023)** - Feature set influence and user interaction design
- **PHP.net (2022)** - Security best practices and implementation patterns
- **W3Schools (2021)** - Technical implementation guidance

## Student Information

**Student Numbers**: ST10451774 & ST10452404  
**Names**: Acazia Ammon & Masike Jr Rasenyalo  
**Declaration**: This code is our own work except where referenced

## Assignment Requirements Met

### Part 1 Requirements
- Research and planning documentation
- ERD diagrams and database design
- User interface mockups and sitemaps

### Part 2 Requirements
- Complete functional prototype
- All CRUD operations implemented
- User authentication and authorization
- Shopping cart and checkout system
- Admin verification system
- Messaging functionality

### Additional Features
- Responsive mobile-friendly design
- Professional UI/UX
- Security best practices
- Error handling and validation
- File upload capabilities
- Database-based cart system

## Testing

### Test Scenarios
1. **User Registration**: Test all validation rules
2. **Login System**: Test role-based access
3. **Admin Functions**: Verify user management
4. **Seller Functions**: Test product management
5. **Shopping Cart**: Test cart operations
6. **Checkout Process**: Test complete order flow
7. **Messaging**: Test user communication

### Browser Compatibility
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Mobile Features

- Touch-friendly interface
- Responsive navigation
- Optimized forms for mobile
- Mobile-optimized product grid
- Swipe-friendly image galleries

## Performance Optimizations

- Efficient database queries
- Image optimization
- Minimal JavaScript footprint
- CSS optimization
- Proper caching headers

## Maintenance

### Regular Tasks
- Database backups
- Log file monitoring
- Security updates
- Performance monitoring

### Troubleshooting
- Check error logs for database issues
- Verify file permissions for uploads
- Ensure proper PHP configuration
- Monitor server resources

## Support

For technical support or questions about this project:
- **Email**: support@pastimes.com
- **Documentation**: Refer to inline code comments
- **Database**: Check loadClothingStore.php for schema details

---

**© 2024 Pastimes Second-Hand Fashion. All rights reserved.**  
**Student Project: ST10451774 & ST10452404**
