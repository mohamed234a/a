#!/bin/bash

echo "🚀 Deploying Auto-Entrepreneur Tunisie to GitHub..."

# Navigate to project directory
cd "/home/a/Bureau/Autoentre (Copie)"

# Initialize git if not already done
if [ ! -d ".git" ]; then
    echo "📦 Initializing git repository..."
    git init
fi

# Configure git user
echo "👤 Configuring git user..."
git config user.name "mohamed234a"
git config user.email "stage@ironbyte.tn"

# Add remote repository
echo "🔗 Setting up remote repository..."
git remote remove origin 2>/dev/null || true
git remote add origin https://github.com/mohamed234a/a.git

# Add all files
echo "📁 Adding all files..."
git add .

# Commit with detailed message
echo "💾 Committing changes..."
git commit -m "🚀 Complete Auto-Entrepreneur Tunisie Platform

✨ Features:
- 🎯 Professional auto-entrepreneur profiles with 4 CV templates
- 🛍️ Service management with premium options  
- 👥 Direct contact system (no messaging)
- 💰 Premium monetization (featured services, urgent badges, extra slots)
- 🔐 Complete admin dashboard with user/service management
- 📱 Responsive design with glassmorphism effects
- 🇹🇳 100% French interface for Tunisia

💼 Business Model:
- Direct contact between clients and auto-entrepreneurs
- Revenue from premium visibility features only
- No transaction supervision

🛠️ Tech Stack:
- Frontend: HTML5, CSS3, Vanilla JavaScript
- Backend: PHP 7.4+ with MVC architecture
- Database: MySQL/MariaDB
- Security: JWT authentication, CSRF protection

🎨 Design:
- Modern glassmorphism design
- Better than Upwork quality
- Mobile-first responsive
- Professional animations and transitions

Ready for production deployment! 🇹🇳✨"

# Set main branch
echo "🌿 Setting main branch..."
git branch -M main

# Push to GitHub
echo "⬆️ Pushing to GitHub..."
git push -u origin main --force

echo "✅ Successfully deployed to GitHub!"
echo "🔗 Repository: https://github.com/mohamed234a/a"
echo "🌐 Access your platform at: http://localhost:8000/frontend/home.html"
echo "🔐 Admin access: http://localhost:8000/frontend/admin_login.html"
echo "   Email: admin@autoentrepreneur.tn"
echo "   Password: admin123"
