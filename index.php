<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="icon" type="image/png" href="logo.png">
  <title>SecondBrain - Smart Reminder Management System</title>
  <meta name="description" content="Professional reminder management system. Never miss important tasks with our intelligent scheduling and notification system.">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0f1419 0%, #1a2332 50%, #2d3748 100%);
      color: white;
      line-height: 1.6;
      overflow-x: hidden;
    }

    html {
      scroll-behavior: smooth;
    }

    /* Header */
    .header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: rgba(15, 20, 25, 0.95);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(31, 116, 231, 0.1);
      z-index: 1000;
      padding: 1rem 0;
    }

    .nav-container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 2rem;
    }

    .logo {
      font-size: 1.5rem;
      font-weight: 700;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: #1f74e7;
    }

    .logo i {
      font-size: 1.3rem;
      color: #1f74e7;
    }

    .logo span {
      color: #1f74e7;
    }

    .nav-buttons {
      display: flex;
      gap: 1rem;
    }

    .nav-btn {
      padding: 0.5rem 1.5rem;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .nav-btn.secondary {
      color: #AAB4C3;
      border: 1px solid rgba(170, 180, 195, 0.3);
    }

    .nav-btn.primary {
      background: #1f74e7;
      color: white;
    }

    .nav-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(31, 116, 231, 0.2);
    }

    /* Hero Section */
    .hero {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 120px 2rem 80px;
      position: relative;
      background: radial-gradient(circle at 30% 20%, rgba(31, 116, 231, 0.1) 0%, transparent 50%),
                  radial-gradient(circle at 70% 80%, rgba(32, 201, 151, 0.1) 0%, transparent 50%);
    }

    .hero-content {
      max-width: 1200px;
      width: 100%;
      text-align: center;
    }

    .hero h1 {
      font-size: 3.5rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      background: linear-gradient(135deg, #1f74e7 0%, #20c997 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      line-height: 1.2;
    }

    .hero-subtitle {
      font-size: 1.25rem;
      color: #AAB4C3;
      margin-bottom: 2.5rem;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }

    .hero-buttons {
      display: flex;
      justify-content: center;
      gap: 1.5rem;
      margin-bottom: 3rem;
    }

    .btn {
      padding: 1rem 2rem;
      border-radius: 12px;
      text-decoration: none;
      font-weight: 600;
      font-size: 1.1rem;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-primary {
      background: linear-gradient(135deg, #1f74e7 0%, #1455b8 100%);
      color: white;
      box-shadow: 0 4px 20px rgba(31, 116, 231, 0.3);
    }

    .btn-secondary {
      background: rgba(170, 180, 195, 0.1);
      color: #AAB4C3;
      border: 1px solid rgba(170, 180, 195, 0.2);
    }

    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(31, 116, 231, 0.4);
    }

    /* Features Section */
    .features {
      padding: 80px 2rem;
      background: rgba(15, 20, 25, 0.5);
    }

    .features-container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .section-title {
      text-align: center;
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
      color: #1f74e7;
    }

    .section-subtitle {
      text-align: center;
      color: #AAB4C3;
      font-size: 1.1rem;
      margin-bottom: 4rem;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
      margin-top: 3rem;
    }

    .feature-card {
      background: rgba(35, 43, 62, 0.3);
      padding: 2rem;
      border-radius: 16px;
      border: 1px solid rgba(31, 116, 231, 0.1);
      backdrop-filter: blur(10px);
      transition: all 0.3s ease;
    }

    .feature-card:hover {
      transform: translateY(-5px);
      border-color: rgba(31, 116, 231, 0.3);
      box-shadow: 0 8px 30px rgba(31, 116, 231, 0.1);
    }

    .feature-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #1f74e7 0%, #20c997 100%);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1.5rem;
      font-size: 1.5rem;
      color: white;
    }

    .feature-title {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: white;
    }

    .feature-description {
      color: #AAB4C3;
      line-height: 1.6;
    }

    /* Social Section */
    .social-section {
      padding: 60px 2rem;
      text-align: center;
      background: rgba(15, 20, 25, 0.3);
    }

    .social-icons {
      display: flex;
      justify-content: center;
      gap: 2rem;
      margin-top: 2rem;
    }

    .social-icons a {
      color: #AAB4C3;
      font-size: 1.5rem;
      transition: all 0.3s ease;
      text-decoration: none;
      padding: 1rem;
      border-radius: 50%;
      background: rgba(170, 180, 195, 0.1);
    }

    .social-icons a:hover {
      color: #1f74e7;
      transform: translateY(-3px);
      background: rgba(31, 116, 231, 0.1);
    }

    /* Footer */
    .footer {
      padding: 2rem;
      text-align: center;
      background: rgba(15, 20, 25, 0.8);
      border-top: 1px solid rgba(31, 116, 231, 0.1);
    }

    .footer-copyright {
      color: #AAB4C3;
      font-size: 0.9rem;
      opacity: 0.8;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2.5rem;
      }

      .hero-subtitle {
        font-size: 1.1rem;
      }

      .hero-buttons {
        flex-direction: column;
        align-items: center;
      }

      .btn {
        width: 100%;
        max-width: 300px;
        justify-content: center;
      }

      .nav-container {
        padding: 0 1rem;
      }

      .nav-buttons {
        gap: 0.5rem;
      }

      .nav-btn {
        padding: 0.4rem 1rem;
        font-size: 0.9rem;
      }

      .features-grid {
        grid-template-columns: 1fr;
      }

      .section-title {
        font-size: 2rem;
      }
    }

    @media (max-width: 480px) {
      .hero {
        padding: 100px 1rem 60px;
      }

      .hero h1 {
        font-size: 2rem;
      }

      .features {
        padding: 60px 1rem;
      }

      .social-section {
        padding: 40px 1rem;
      }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header class="header">
    <div class="nav-container">
      <a href="#" class="logo">
        <i class="fa-solid fa-brain"></i>
        <span>SecondBrain</span>
      </a>
      <div class="nav-buttons">
        <a href="login.php" class="nav-btn secondary">Login</a>
        <a href="register.php" class="nav-btn primary">Get Started</a>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content">
      <h1>Smart Reminder System</h1>
      <p class="hero-subtitle">
        Never miss important tasks again. Our intelligent scheduling system helps you stay organized 
        and productive with smart notifications and voice reminders.
      </p>
      <div class="hero-buttons">
        <a href="#features" class="btn btn-primary">
          <i class="fas fa-info-circle"></i>
          Learn More
        </a>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features" id="features">
    <div class="features-container">
      <h2 class="section-title">Why Choose SecondBrain?</h2>
      <p class="section-subtitle">
        Powerful features designed to make your life easier and more organized
      </p>
      
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-bell"></i>
          </div>
          <h3 class="feature-title">Smart Notifications</h3>
          <p class="feature-description">
            Get timely reminders through email notifications and never miss important deadlines or appointments.
          </p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-microphone"></i>
          </div>
          <h3 class="feature-title">Voice Reminders</h3>
          <p class="feature-description">
            Record voice notes and reminders for a more personal and convenient way to manage your tasks.
          </p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-calendar-alt"></i>
          </div>
          <h3 class="feature-title">Flexible Scheduling</h3>
          <p class="feature-description">
            Set one-time or recurring reminders with custom schedules that fit your lifestyle.
          </p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h3 class="feature-title">Secure & Private</h3>
          <p class="feature-description">
            Your data is protected with enterprise-grade security and privacy controls.
          </p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-mobile-alt"></i>
          </div>
          <h3 class="feature-title">Mobile Friendly</h3>
          <p class="feature-description">
            Access your reminders from any device with our responsive web interface.
          </p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <h3 class="feature-title">Progress Tracking</h3>
          <p class="feature-description">
            Monitor your productivity and track completion rates with detailed analytics.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Social Section -->
  <section class="social-section">
    <h3>Connect With Us</h3>
    <div class="social-icons">
      <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
      <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
      <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
      <a href="#" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-copyright">
      © 2025 SecondBrain. All rights reserved.
    </div>
  </footer>
</body>
</html>
