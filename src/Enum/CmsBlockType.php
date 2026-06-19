<?php

namespace App\Enum;

enum CmsBlockType: string
{
    case Text = 'TEXT';
    case Image = 'IMAGE';
    case Video = 'VIDEO';
    case Banner = 'BANNER';
    case Slider = 'SLIDER';
    case Html = 'HTML';
    case Products = 'PRODUCTS';
    case Categories = 'CATEGORIES';
    case Faq = 'FAQ';
    case Testimonials = 'TESTIMONIALS';
    case Button = 'BUTTON';
    case Spacer = 'SPACER';
}
