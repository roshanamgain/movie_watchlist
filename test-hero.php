<!DOCTYPE html>
<html>
<head>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        .full-screen-hero {
            width: 100%;
            height: 100vh;
            background-image: url('images/bg-image.avif');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
        }
        
        .full-screen-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
        }
        
        h1 {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        p {
            font-size: 1.2rem;
        }
        
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 32px;
            background: #c41e3a;
            color: white;
            text-decoration: none;
            border-radius: 40px;
        }
    </style>
</head>
<body>
    <div class="full-screen-hero">
        <div class="hero-content">
            <h1>La Casa de Papel</h1>
            <p>Track films you've watched. Save those you want to see.</p>
            <a href="#" class="btn">Join the Heist</a>
        </div>
    </div>
    
    <div style="padding: 40px; text-align: center;">
        <h2>Content Below Hero</h2>
        <p>If you see this, the hero is working!</p>
    </div>
</body>
</html>