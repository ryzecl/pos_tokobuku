<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daebook - Discover Books</title>
    <!-- Font diganti jadi Poppins seperti code 2 -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg: #0B0B15;
            --card: #13132B;
            --accent: #8B5CF6;
            --primary: #A78BFA;
            --secondary: #EC4899;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: white;
            overflow-x: hidden;
        }

        .container {
            max-width: 1500px;
            margin: 0 auto;
            /* padding: 0 1.5rem; */
        }

        /* Navbar */
        #navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 50;
            padding: 1.5rem 0;
            transition: all 0.3s;
        }

        #navbar.scrolled {
            background: rgba(11, 11, 21, 0.8);
            backdrop-filter: blur(12px);
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .navbar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo-icon {
            background: white;
            color: var(--bg);
            padding: 0.375rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .nav-links {
            display: none;
            gap: 2rem;
        }

        @media (min-width: 768px) {
            .nav-links {
                display: flex;
            }
        }

        .nav-link {
            color: #D1D5DB;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: white;
        }

        .nav-cta {
            display: none;
        }

        @media (min-width: 768px) {
            .nav-cta {
                display: block;
            }
        }

        .btn-primary {
            background: var(--accent);
            color: white;
            padding: 0.625rem 1.5rem;
            border-radius: 9999px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 0 15px rgba(139, 92, 246, 0.5);
        }

        .btn-primary:hover {
            background: #7C3AED;
        }

        .mobile-toggle {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            display: block;
        }

        @media (min-width: 768px) {
            .mobile-toggle {
                display: none;
            }
        }

        .mobile-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: var(--card);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            flex-direction: column;
            gap: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .mobile-menu.active {
            display: flex;
        }

        .mobile-link {
            color: #D1D5DB;
            text-decoration: none;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .mobile-btn {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
        }

        /* home */
        .home {
            position: relative;
            padding-top: 8rem;
            padding-bottom: 5rem;
            min-height: 100vh;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .home-bg-blur {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            z-index: -1;
        }

        .home-blur-1 {
            top: -20%;
            left: -10%;
            width: 600px;
            height: 600px;
            background: rgba(139, 92, 246, 0.2);
        }

        .home-blur-2 {
            bottom: 0;
            right: -10%;
            width: 500px;
            height: 500px;
            background: rgba(236, 72, 153, 0.1);
        }

        .home-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
            align-items: center;
        }

        @media (min-width: 1024px) {
            .home-container {
                grid-template-columns: 8fr 12fr;
            }
        }

        .home-title {
            font-size: 3rem;
            font-weight: bold;
            line-height: 1.1;
            margin-bottom: 2rem;
        }

        @media (min-width: 768px) {
            .home-title {
                font-size: 4.5rem;
            }
        }

        .home h1 span {
            color: var(--light-primary);
        }

        .home-description {
            color: #9CA3AF;
            font-size: 1.125rem;
            line-height: 1.75;
            max-width: 32rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 768px) {
            .home-description {
                font-size: 1.25rem;
            }
        }

        .home-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .btn-home-primary {
            background: var(--primary);
            color: black;
            padding: 1rem 2rem;
            border-radius: 9999px;
            border: none;
            font-weight: bold;
            font-size: 1.125rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 0 20px rgba(167, 139, 250, 0.4);
        }

        .btn-home-primary:hover {
            background: white;
        }

        .btn-home-secondary {
            padding: 1rem 2rem;
            border-radius: 9999px;
            background: transparent;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-home-secondary:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .home-image {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .image-container {
            position: relative;
            width: 100%;
            max-width: 28rem;
            aspect-ratio: 1;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .glow-platform {
            position: absolute;
            bottom: 2.5rem;
            left: 50%;
            transform: translateX(-50%);
            width: 75%;
            height: 6rem;
            background: rgba(139, 92, 246, 0.3);
            border-radius: 100%;
            filter: blur(40px);
        }

        .book-image {
            position: relative;
            z-index: 10;
            width: 100%;
            height: 100%;
        }

        .book-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 25px 50px rgba(0, 0, 0, 0.5));
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .particle-1 {
            top: 25%;
            left: -2.5rem;
            width: 1rem;
            height: 1rem;
            background: var(--secondary);
            filter: blur(2px);
        }

        .particle-2 {
            bottom: 33%;
            right: -1.25rem;
            width: 1.5rem;
            height: 1.5rem;
            background: var(--primary);
            filter: blur(4px);
            animation-delay: 700ms;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Publishers */
        .publishers {
            background: #0E0E1C;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 2rem 0;
        }

        .publishers-label {
            color: #6B7280;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }

        .publishers-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            opacity: 0.5;
            filter: grayscale(1);
            transition: all 0.5s;
        }

        .publishers-grid:hover {
            opacity: 1;
            filter: grayscale(0);
        }

        .publisher-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
        }

        .publisher-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            font-family: serif;
            letter-spacing: 0.1em;
            transition: color 0.3s;
        }

        @media (min-width: 768px) {
            .publisher-name {
                font-size: 1.875rem;
            }
        }

        .publisher-item:hover .publisher-name {
            color: var(--primary);
        }

        .publisher-sub {
            font-size: 0.625rem;
            color: #6B7280;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            margin-top: 0.25rem;
        }

        /* About */
        .about {
            padding: 6rem 0;
            background: var(--bg);
            position: relative;
        }

        .about-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 4rem;
            align-items: center;
        }

        @media (min-width: 768px) {
            .about-container {
                grid-template-columns: 1fr 1fr;
            }
        }

        .about-image {
            position: relative;
        }

        .about-image-bg {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top right, var(--accent), var(--secondary));
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            filter: blur(40px);
            opacity: 0.2;
            animation: pulse 4s ease-in-out infinite;
        }

        .about-img {
            position: relative;
            z-index: 10;
            width: 80%;
            /* border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; */
            /* border: 4px solid rgba(255, 255, 255, 0.05); */
            object-fit: cover;
            /* height: 400px*/
        }

        @media (min-width: 768px) {
            .about-img {
                height: 500px;
                width: auto;
            }
        }

        .about-content {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .section-label {
            color: var(--primary);
            font-weight: 500;
            letter-spacing: 0.05em;
        }

        .section-title {
            font-size: 2.25rem;
            font-weight: bold;
            line-height: 1.2;
        }

        @media (min-width: 768px) {
            .section-title {
                font-size: 3rem;
            }
        }

        .section-description {
            color: #9CA3AF;
            font-size: 1.125rem;
            line-height: 1.75;
        }

        .features-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding-top: 1rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #D1D5DB;
        }

        .feature-icon {
            color: var(--secondary);
        }

        .about-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 2rem;
            margin-top: 2rem;
        }

        .about-thank {
            font-weight: 500;
            color: white;
        }

        .about-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .mini-logo {
            width: 1.5rem;
            height: 1.5rem;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: black;
            font-weight: bold;
            font-size: 0.75rem;
        }

        /* Book Search */
        .book-search {
            padding: 5rem 0;
            position: relative;
            overflow: hidden;
        }

        .star-deco {
            position: absolute;
            font-size: 9rem;
            opacity: 0.2;
            color: var(--card);
            pointer-events: none;
        }

        .star-left {
            top: 5rem;
            left: 2.5rem;
            transform: rotate(12deg);
        }

        .star-right {
            bottom: 5rem;
            right: 2.5rem;
            transform: rotate(-12deg);
        }

        .search-header {
            text-align: center;
            max-width: 42rem;
            margin: 0 auto 4rem;
        }

        .section-label-gray {
            color: #9CA3AF;
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }

        .section-title-center {
            font-size: 2.25rem;
            font-weight: bold;
            margin-bottom: 2rem;
        }

        @media (min-width: 768px) {
            .section-title-center {
                font-size: 3rem;
            }
        }

        .search-wrapper {
            width: 100%;
            position: relative;
        }

        .search-glow {
            position: absolute;
            inset: -4px;
            background: linear-gradient(to right, var(--accent), var(--secondary));
            border-radius: 0.5rem;
            filter: blur(8px);
            opacity: 0.25;
            transition: opacity 1s;
        }

        .search-wrapper:hover .search-glow {
            opacity: 0.5;
        }

        .search-form {
            position: relative;
            display: flex;
            align-items: center;
            background: #13132B;
            border-radius: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.5rem;
        }

        .search-input {
            width: 100%;
            background: transparent;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            outline: none;
        }

        .search-input::placeholder {
            color: #6B7280;
        }

        .search-btn {
            background: var(--accent);
            color: white;
            padding: 0.75rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .search-btn:hover {
            background: #7C3AED;
        }

        .search-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .ai-results {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 4rem;
        }

        @media (min-width: 768px) {
            .ai-results {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .book-card {
            background: rgba(19, 19, 43, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 1rem;
            transition: border 0.3s;
        }

        .book-card:hover {
            border-color: rgba(167, 139, 250, 0.5);
        }

        .book-genre {
            font-size: 0.75rem;
            font-weight: bold;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .book-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: white;
            margin-bottom: 0.25rem;
        }

        .book-author {
            font-size: 0.875rem;
            color: #9CA3AF;
            margin-bottom: 1rem;
        }

        .book-description {
            color: #D1D5DB;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .book-details {
            color: var(--primary);
            font-size: 0.875rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            transition: gap 0.3s;
            background: none;
            border: none;
            cursor: pointer;
        }

        .book-details:hover {
            gap: 0.5rem;
        }

        /* Genre Cards */
        .genre-cards {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 1024px) {
            .genre-cards {
                grid-template-columns: 1fr 1fr;
            }
        }

        .genre-card {
            background: #13132B;
            border-radius: 1.5rem;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: border 0.3s;
        }

        .genre-card:hover {
            border-color: rgba(167, 139, 250, 0.3);
        }

        .genre-watermark {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 100px;
            font-weight: bold;
            color: rgba(35, 35, 66, 0.5);
            line-height: 1;
            user-select: none;
        }

        .genre-content {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .genre-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
        }

        .genre-count {
            font-size: 3.75rem;
            font-weight: bold;
        }

        .genre-primary {
            color: var(--primary);
        }

        .genre-secondary {
            color: var(--secondary);
        }

        .genre-badges {
            display: flex;
            gap: 0.5rem;
        }

        .badge {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.75rem;
        }

        .badge-black {
            background: black;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .badge-orange {
            background: #F97316;
        }

        .badge-blue {
            background: #3B82F6;
        }

        .genre-books {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .book-cover {
            width: 6rem;
            height: 9rem;
            object-fit: cover;
            border-radius: 0.375rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s;
        }

        .book-left {
            transform: rotate(-6deg) translateY(1rem);
        }

        .book-center {
            z-index: 10;
            transform: scale(1.1);
        }

        .book-right {
            transform: rotate(6deg) translateY(1rem);
        }

        .genre-card:hover .book-left,
        .genre-card:hover .book-right {
            transform: translateY(0);
        }

        .genre-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .genre-text {
            max-width: 200px;
        }

        .genre-text p {
            color: white;
            font-weight: 500;
        }

        .text-secondary {
            color: var(--secondary);
        }

        .text-primary {
            color: var(--primary);
        }

        .genre-explore {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .genre-explore:hover {
            transform: translateX(0.25rem);
        }

        /* Testimonials */
        .testimonials {
            padding: 6rem 0;
            background: linear-gradient(to bottom, #0B0B15, #13132B);
        }

        .testimonials-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-label-white {
            color: white;
            font-weight: 500;
        }

        .text-gray {
            color: #9CA3AF;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 1024px) {
            .testimonials-grid {
                grid-template-columns: 1.5fr 6fr 4fr;
            }
        }

        .avatars-column {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .avatar {
            width: 100%;
            aspect-ratio: 1;
            border-radius: 1rem;
            object-fit: cover;
            opacity: 0.5;
            transition: opacity 0.3s;
            cursor: pointer;
        }

        .avatar:hover,
        .avatar-active {
            opacity: 1;
        }

        .avatar-active {
            box-shadow: 0 0 0 4px var(--primary);
        }

        .testimonial-content {
            background: #1A1A35;
            border-radius: 1.5rem;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .quote-mark {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 200px;
            line-height: 1;
            color: rgba(35, 35, 66, 0.5);
            font-family: serif;
            opacity: 0.5;
            user-select: none;
        }

        .testimonial-text {
            position: relative;
            z-index: 10;
        }

        .testimonial-quote {
            font-size: 1.875rem;
            font-weight: bold;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        .testimonial-description {
            color: #9CA3AF;
            font-size: 1.125rem;
            margin-bottom: 2rem;
        }

        .testimonial-author {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1.5rem;
            position: relative;
            z-index: 10;
        }

        .author-name {
            font-size: 1.25rem;
            font-weight: bold;
            color: white;
        }

        .author-role {
            font-size: 0.875rem;
            color: #6B7280;
        }

        .rating {
            display: flex;
            gap: 0.25rem;
        }

        .star-filled {
            color: var(--primary);
            fill: var(--primary);
        }

        .stats-column {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .stat-card {
            background: #13132B;
            padding: 2rem;
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-number {
            font-size: 2.25rem;
            font-weight: bold;
            color: #E5E7EB;
            margin-bottom: 0.5rem;
        }

        .stat-secondary {
            color: var(--secondary);
        }

        .stat-gray {
            color: #9CA3AF;
        }

        .stat-label {
            color: #6B7280;
        }

        /* Footer */
        .footer {
            background: #3B3B98;
            color: #0F0F1A;
            padding: 5rem 0 2.5rem;
        }

        .footer-logo-mobile {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2.5rem;
        }

        @media (min-width: 768px) {
            .footer-logo-mobile {
                display: none;
            }
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 3rem;
            margin-bottom: 3rem;
        }

        @media (min-width: 768px) {
            .footer-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .footer-title {
            font-weight: bold;
            color: black;
            font-size: 1.125rem;
            margin-bottom: 1rem;
        }

        .footer-text {
            color: #0F0F1A;
            font-size: 0.875rem;
            line-height: 1.75;
            margin-bottom: 0.5rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-link {
            background: #0B0B15;
            padding: 0.5rem;
            border-radius: 0.375rem;
            color: white;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }

        .social-link:hover {
            background: white;
            color: var(--accent);
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: #0F0F1A;
            font-size: 0.875rem;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: black;
        }

        /* .newsletter-form {
            display: flex;
            background: white;
            padding: 0.25rem;
            border-radius: 0.5rem;
        } */
             .newsletter input {
            padding: 13px 10px;
            border: none;
            border-radius: 50px 0 0 50px;
            width: 70%;
            font-size: 16px;
        }

        .newsletter button {
            padding: 16px 20px;
            border: none;
            background: #000;
            color: #fff;
            border-radius: 0 50px 50px 0;
            cursor: pointer;
            font-weight: 600;
        }

        .newsletter-input {
            background: transparent;
            color: #1F2937;
            padding: 0.75rem 0.75rem;
            font-size: 0.875rem;
            border: none;
            outline: none;
            flex: 1;
        }

        .newsletter-input::placeholder {
            color: #9CA3AF;
        }

        .newsletter-btn {
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }

        .newsletter-btn:hover {
            background: #7C3AED;
        }

        .footer-copyright {
            text-align: center;
            color: #0F0F1A;
            font-size: 0.875rem;
        }

        /* Animations */
        .fade-in-left {
            animation: fadeInLeft 0.8s ease-out;
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .fade-in-right {
            animation: fadeInRight 0.8s ease-out 0.2s both;
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .fade-in-view {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease-out;
        }

        .fade-in-view.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #0B0B15;
        }

        ::-webkit-scrollbar-thumb {
            background: #4C1D95;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #6D28D9;
        }
    </style>
</head>

<body>
    <nav id="navbar">
        <div class="container navbar-container">
            <!-- <div class="logo">
                <div class="logo-icon"><i data-lucide="book-open"></i></div>
                <span class="logo-text">Daebook</span>
            </div> -->
            <div class="logo">
                <img src="assets/img/logo/logo.png" alt="Daebook Logo">
            </div>
            <div class="nav-links">
                <a href="#home" class="nav-link">Home</a>
                <a href="#about" class="nav-link">About</a>
                <a href="#categories" class="nav-link">All Books</a>
                <a href="#reviews" class="nav-link">Reviews</a>
                <div class="nav-cta">
                <button class="btn-primary">Log In</button>
            </div>
            </div>
            
            <button class="mobile-toggle" id="mobile-toggle">
                <i data-lucide="menu" id="menu-icon"></i>
                <i data-lucide="x" id="close-icon" style="display: none;"></i>
            </button>
        </div>
        <div class="mobile-menu" id="mobile-menu">
            <a href="#home" class="mobile-link">Home</a>
            <a href="#about" class="mobile-link">About</a>
            <a href="#categories" class="mobile-link">All Books</a>
            <a href="#reviews" class="mobile-link">Reviews</a>
            <a href="login.php"><button class="btn-primary mobile-btn">Log In</button></a>
        </div>
    </nav>

    <main>
        <section id="home" class="home">
            <div class="home-bg-blur home-blur-1"></div>
            <div class="home-bg-blur home-blur-2"></div>
            <div class="container home-container">
                <div class="home-content fade-in-left">
                    <!-- <h1 class="home-title">Discover Books <br><span class="gradient-text">Worth Your Time</span> <br>Today.</h1> -->
                    <h1>Discover Books<br><span>Worth</span> Your <span>Time</span><br>Today.</h1>
                    <p class="home-description">Curated books to sharpen thinking, spark curiosity, and inspire meaningful personal growth.</p>
                    <div class="home-buttons">
                        <a href="#categories"><button class="btn-home-primary">Explore More Now!</button></a>
                        <button class="btn-home-secondary">Find Books</button>
                    </div>
                </div>
                <div class="home-image fade-in-right">
                    <div class="image-container">
                        <div class="glow-platform"></div>
                        <div class="book-image">
                            <img src="assets/img/table-book.png" alt="Magic Book" class="book-img">
                            <div class="particle particle-1"></div>
                            <div class="particle particle-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="publishers">
            <div class="container">
                <p class="publishers-label">Featured Publishers</p>
                <div class="publishers-grid " id="about">
                    <div class="publisher-item">
                        <a href="https://www.gramedia.com/" target="_blank" rel="noopener noreferrer"><img src="assets/img/logo/gramedia-logo.png" alt="Gramedia"></a>
                    </div>
                    <div class="publisher-item">
                        <a href="https://mizan.com/" target="_blank" rel="noopener noreferrer"><img src="assets/img/logo/mizan-logo.png" alt="Mizan"></a>
                    </div>
                    <div class="publisher-item">
                        <a href="https://bintangpustaka.com/" target="_blank" rel="noopener noreferrer"><img src="assets/img/logo/bentang-pustaka-logo.png" alt="Bentang"></a>
                    </div>
                    <div class="publisher-item">
                        <a href="https://penerbitdeepublish.com/" target="_blank" rel="noopener noreferrer"><img src="assets/img/logo/deepublish-logo.png" alt="Deepublish"></a>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="about">
            <div class="container about-container">
                <div class="about-image fade-in-view">
                    <div class="about-image-bg"></div>
                    <img src="assets/img/about-us.png" alt="Couple reading" class="about-img">
                </div>
                <div class="about-content fade-in-view">
                    <span class="section-label">About Us</span>
                    <h2 class="section-title">Daebook: A Smarter Way to Discover, Buy, and Experience Books</h2>
                    <p class="section-description">Daebook exists for readers who seek more than just finishing a book — they read to expand their thinking.</p>
                    <div class="features-list">
                        <div class="feature-item"><i data-lucide="check-circle-2" class="feature-icon"></i><span>Intelligent discovery features</span></div>
                        <div class="feature-item"><i data-lucide="check-circle-2" class="feature-icon"></i><span>Seamless buying experience</span></div>
                        <div class="feature-item"><i data-lucide="check-circle-2" class="feature-icon"></i><span>Growth as a natural part of life</span></div>
                    </div>
                    <div class="about-footer">
                        <span class="about-thank">Thank You for Choosing Daebook &lt;3</span>
                        <div class="about-logo">
                            <img src="assets/img/logo/logo.png" alt="Daebook Logo">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="categories" class="book-search">
            <div class="star-deco star-left">*</div>
            <div class="star-deco star-right">*</div>
            <div class="container">
                <div class="search-header">
                    <span class="section-label-gray">Book Genres</span>
                    <h2 class="section-title-center">Explore different type book <br> and genres</h2>
                    <div class="search-wrapper">
                        <div class="search-glow"></div>
                        <form class="search-form" id="search-form">
                            <input type="text" id="search-input" placeholder="Search all books: e.g. 'The Da Vinci Code' or 'Cyberpunk Novels'" class="search-input">
                            <button type="submit" class="search-btn" id="search-btn">
                                <i data-lucide="search" id="search-icon"></i>
                                <i data-lucide="loader-2" id="loader-icon" class="spin" style="display: none;"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="ai-results" id="ai-results"></div>
                <div class="genre-cards">
                    <div class="genre-card">
                        <div class="genre-watermark">100K+</div>
                        <div class="genre-content">
                            <div class="genre-header">
                                <h3 class="genre-count genre-primary">100K+</h3>
                                <div class="genre-badges">
                                    <div class="badge badge-black">A</div>
                                    <div class="badge badge-orange">5+</div>
                                </div>
                            </div>
                            <div class="genre-books">
                                <img src="assets/img/books/romance-1.jpg" class="book-cover book-left" alt="Romance 1">
                                <img src="assets/img/books/romance-2.jpg" class="book-cover book-center" alt="Romance 2">
                                <img src="assets/img/books/romance-3.jpg" class="book-cover book-right" alt="Romance 3">
                            </div>
                            <div class="genre-footer">
                                <div class="genre-text">
                                    <p>#1 The best-selling genre on Daebook: <span class="text-secondary">Romance.</span></p>
                                </div>
                                <button class="genre-explore text-primary">Explore Now <i data-lucide="arrow-right"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="genre-card">
                        <div class="genre-watermark">60K+</div>
                        <div class="genre-content">
                            <div class="genre-header">
                                <h3 class="genre-count genre-secondary">60K+</h3>
                                <div class="genre-badges">
                                    <div class="badge badge-black">E</div>
                                    <div class="badge badge-blue">5+</div>
                                </div>
                            </div>
                            <div class="genre-books">
                                <img src="assets/img/books/thriller-1.jpg" class="book-cover book-left" alt="Thriller 1">
                                <img src="assets/img/books/thriller-2.jpg" class="book-cover book-center" alt="Thriller 2">
                                <img src="assets/img/books/thriller-3.jpg" class="book-cover book-right" alt="Thriller 3">
                            </div>
                            <div class="genre-footer">
                                <div class="genre-text">
                                    <p>#2 Best-selling genre on Daebook: <span class="text-primary">Thriller.</span></p>
                                </div>
                                <button class="genre-explore text-secondary">Explore Now <i data-lucide="arrow-right"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="reviews" class="testimonials">
            <div class="container">
                <div class="testimonials-header">
                    <span class="section-label-white">Customer Trust</span>
                    <h2 class="section-title-center">Building Trust Through Real <br><span class="text-gray">Customer Experiences</span></h2>
                </div>
                <div class="testimonials-grid">
                    <div class="avatars-column">
                        <img src="assets/img/testimonial/person1.jpg" class="avatar" alt="User 1">
                        <img src="assets/img/testimonial/person2.jpg" class="avatar avatar-active" alt="User 2">
                        <img src="assets/img/testimonial/person3.jpg" class="avatar" alt="User 3">
                    </div>
                    <div class="testimonial-content">
                        <div class="quote-mark">"</div>
                        <div class="testimonial-text">
                            <h3 class="testimonial-quote">"Grateful for the Books That Changed Our Access to Knowledge."</h3>
                            <p class="testimonial-description">We're grateful because your books broaden our knowledge, improve study access, and make learning experiences richer for everyone.</p>
                        </div>
                        <div class="testimonial-author">
                            <div>
                                <h4 class="author-name">Makayla Barron</h4>
                                <p class="author-role">Harvard University Student's</p>
                            </div>
                            <div class="rating">
                                <i data-lucide="star" class="star-filled"></i>
                                <i data-lucide="star" class="star-filled"></i>
                                <i data-lucide="star" class="star-filled"></i>
                                <i data-lucide="star" class="star-filled"></i>
                                <i data-lucide="star" class="star-filled"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stats-column">
                        <div class="stat-card">
                            <h4 class="stat-number">999K+</h4>
                            <p class="stat-label">People Chose Us</p>
                        </div>
                        <div class="stat-card">
                            <h4 class="stat-number stat-secondary">97%</h4>
                            <p class="stat-label">Readers Feel Satisfied</p>
                        </div>
                        <div class="stat-card">
                            <h4 class="stat-number stat-gray">86%</h4>
                            <p class="stat-label">Readers Come Back Again</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-logo-mobile">
                <div class="logo-icon"><i data-lucide="book-open"></i></div>
                <span class="logo-text">Daebook</span>
            </div>
            <div class="footer-grid">
                <div class="footer-column">
                    <h4 class="footer-title">Contact Information</h4>
                    <p class="footer-text">Jl. Pangkal Perjuangan By Pass<br>Tanjungpura, Karawang, Jawa Barat</p>
                    <p class="footer-text">daebook.work@gmail.com</p>
                    <p class="footer-text">+1 (212) 555-0198</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i data-lucide="facebook"></i></a>
                        <a href="#" class="social-link"><i data-lucide="linkedin"></i></a>
                        <a href="#" class="social-link"><i data-lucide="twitter"></i></a>
                        <a href="#" class="social-link"><i data-lucide="instagram"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h4 class="footer-title">Daebook Pages</h4>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#categories">Categories</a></li>
                        <li><a href="#reviews">Reviews</a></li>
                        <li><a href="#">Log In</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4 class="footer-title">Our Team</h4>
                    <ul class="footer-links">
                        <li>Fhazar R A - PM</li>
                        <li>Fadel R - QA</li>
                        <li>Hildan A T P - Frontend</li>
                        <li>Ferry N H - Backend</li>
                        <li>Daffa N Z - UI/UX</li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4 class="footer-title">Get Latest Update</h4>
                    <p class="footer-text">Sign up now to receive fresh updates, product releases, and news you would regret missing later.</p>
                    <!-- <form class="newsletter-form">
                        <input type="email" placeholder="Enter your email.." class="newsletter-input">
                        <button type="submit" class="newsletter-btn">Sign Up</button>
                    </form> -->
                    <div class="newsletter" style="margin-top:25px;">
                    <input type="email" placeholder="Enter your email">
                    <button>Sign Up</button>
                </div>
                </div>
            </div>
            <div class="footer-copyright">Copyright Daebook © 2025 All Rights Reserved.</div>
        </div>
    </footer>

    <!-- Script tetap sama seperti code 1 -->
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Mobile Menu Toggle
        const mobileToggle = document.getElementById('mobile-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('menu-icon');
        const closeIcon = document.getElementById('close-icon');

        mobileToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
            if (mobileMenu.classList.contains('active')) {
                menuIcon.style.display = 'none';
                closeIcon.style.display = 'block';
            } else {
                menuIcon.style.display = 'block';
                closeIcon.style.display = 'none';
            }
            lucide.createIcons();
        });

        // Close mobile menu on link click
        document.querySelectorAll('.mobile-link').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                menuIcon.style.display = 'block';
                closeIcon.style.display = 'none';
            });
        });

        // Navbar Scroll Effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in-view').forEach(el => {
            observer.observe(el);
        });

        // Book Search with API
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const searchBtn = document.getElementById('search-btn');
        const searchIcon = document.getElementById('search-icon');
        const loaderIcon = document.getElementById('loader-icon');
        const aiResults = document.getElementById('ai-results');

        searchForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const query = searchInput.value.trim();

            if (!query) return;

            // Show loading
            searchBtn.disabled = true;
            searchIcon.style.display = 'none';
            loaderIcon.style.display = 'block';
            lucide.createIcons();

            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        query
                    })
                });

                const data = await response.json();

                if (data.success && data.books) {
                    displayBooks(data.books);
                } else {
                    aiResults.innerHTML = '<p style="color: #EF4444; text-align: center;">Error: ' + (data.error || 'Failed to fetch recommendations') + '</p>';
                }
            } catch (error) {
                aiResults.innerHTML = '<p style="color: #EF4444; text-align: center;">Network error. Please try again.</p>';
            } finally {
                searchBtn.disabled = false;
                searchIcon.style.display = 'block';
                loaderIcon.style.display = 'none';
                lucide.createIcons();
            }
        });

        function displayBooks(books) {
            if (!books || books.length === 0) {
                aiResults.innerHTML = '';
                return;
            }

            const html = books.map(book => `
                <div class="book-card">
                    <div class="book-genre">${book.genre || 'General'}</div>
                    <h3 class="book-title">${book.title}</h3>
                    <p class="book-author">by ${book.author}</p>
                    <p class="book-description">${book.description}</p>
                    <button class="book-details">Details <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i></button>
                </div>
            `).join('');

            aiResults.innerHTML = html;
            lucide.createIcons();
        }
    </script>
</body>

</html>