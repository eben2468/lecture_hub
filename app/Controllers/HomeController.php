<?php
/**
 * ============================================================
 * Nadics LectureHub — Home Controller
 * ============================================================
 *
 * Handles public-facing pages: landing page, about, contact,
 * and features.
 *
 * @package    NadicsLectureHub
 * @subpackage App\Controllers
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace App\Controllers;

use Core\Controller;
use Core\Request;

class HomeController extends Controller
{
    /**
     * Display the landing page.
     *
     * @param  Request $request
     * @return void
     */
    public function index(Request $request): void
    {
        $this->view('home.index', [
            'page_title'       => 'Welcome',
            'page_description' => 'Nadics LectureHub — Smart Lecture Management System. Every Student Hears. Every Lecture Lives.',
        ]);
    }

    /**
     * Display the about page.
     *
     * @param  Request $request
     * @return void
     */
    public function about(Request $request): void
    {
        $this->view('home.about', [
            'page_title'       => 'About Us',
            'page_description' => 'Learn about Nadics Solutions and our Smart Lecture Management System.',
        ]);
    }

    /**
     * Display the contact page.
     *
     * @param  Request $request
     * @return void
     */
    public function contact(Request $request): void
    {
        $this->view('home.contact', [
            'page_title'       => 'Contact Us',
            'page_description' => 'Get in touch with Nadics Solutions.',
        ]);
    }

    /**
     * Display the features page.
     *
     * @param  Request $request
     * @return void
     */
    public function features(Request $request): void
    {
        $this->view('home.features', [
            'page_title'       => 'Features',
            'page_description' => 'Explore the powerful features of Nadics LectureHub.',
        ]);
    }

    /**
     * Display the pricing page.
     */
    public function pricing(Request $request): void
    {
        $this->view('home.pricing', [
            'page_title'       => 'Institutional Pricing',
            'page_description' => 'Flexible deployment plans for universities, faculties, and departments.',
        ]);
    }

    /**
     * Display the interactive demo simulator page.
     */
    public function demo(Request $request): void
    {
        $this->view('home.demo', [
            'page_title'       => 'Interactive Live Demo',
            'page_description' => 'Experience live WebRTC audio streaming and QR attendance in action.',
        ]);
    }

    /**
     * Display the public API documentation.
     */
    public function apiDocs(Request $request): void
    {
        $this->view('home.api', [
            'page_title'       => 'Developer API & Webhooks',
            'page_description' => 'API documentation for integrating Nadics LectureHub with institutional ERPs.',
        ]);
    }

    /**
     * Display careers page.
     */
    public function careers(Request $request): void
    {
        $this->view('home.careers', [
            'page_title'       => 'Careers at Nadics',
            'page_description' => 'Join Nadics Solutions in building higher education technology for Africa.',
        ]);
    }

    /**
     * Display blog page.
     */
    public function blog(Request $request): void
    {
        $this->view('home.blog', [
            'page_title'       => 'EdTech Insights Blog',
            'page_description' => 'Articles and engineering insights on classroom technology and bandwidth optimization.',
        ]);
    }

    /**
     * Display help center.
     */
    public function help(Request $request): void
    {
        $this->view('home.help', [
            'page_title'       => 'Help Center & Support',
            'page_description' => 'Guides, FAQs, and support channels for lecturers, students, and administrators.',
        ]);
    }

    /**
     * Display user documentation.
     */
    public function docs(Request $request): void
    {
        $this->view('home.docs', [
            'page_title'       => 'System Documentation',
            'page_description' => 'Complete technical user guides and system documentation.',
        ]);
    }

    /**
     * Display system operational status.
     */
    public function status(Request $request): void
    {
        $this->view('home.status', [
            'page_title'       => 'System Operational Status',
            'page_description' => 'Real-time telemetry and service status across audio streaming edge nodes and database services.',
        ]);
    }

    /**
     * Display community hub.
     */
    public function community(Request $request): void
    {
        $this->view('home.community', [
            'page_title'       => 'Academic Community Hub',
            'page_description' => 'Connect with educators, researchers, and campus administrators using Nadics LectureHub.',
        ]);
    }

    /**
     * Display privacy policy.
     */
    public function privacy(Request $request): void
    {
        $this->view('home.privacy', [
            'page_title'       => 'Privacy Policy',
            'page_description' => 'How Nadics Solutions handles user privacy and student data protection.',
        ]);
    }

    /**
     * Display terms of service.
     */
    public function terms(Request $request): void
    {
        $this->view('home.terms', [
            'page_title'       => 'Terms of Service',
            'page_description' => 'Terms of service and institutional usage agreement for Nadics LectureHub.',
        ]);
    }

    /**
     * Display data governance policy.
     */
    public function dataPolicy(Request $request): void
    {
        $this->view('home.data_policy', [
            'page_title'       => 'Data Governance Policy',
            'page_description' => 'Student data security, encryption standards, and institutional data ownership rules.',
        ]);
    }
}
