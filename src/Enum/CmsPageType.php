<?php

namespace App\Enum;

enum CmsPageType: string
{
    case Home = 'HOME';
    case About = 'ABOUT';
    case Contact = 'CONTACT';
    case Faq = 'FAQ';
    case Terms = 'TERMS';
    case Privacy = 'PRIVACY';
    case Custom = 'CUSTOM';
    case LandingPage = 'LANDING_PAGE';
}
