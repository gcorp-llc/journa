import Link from "next/link";
import { Mail, Phone, MapPin, Globe, Heart } from "lucide-react";

const Footer = () => {
  return (
    <footer className="bg-base-200 text-base-content">
      <div className="container mx-auto py-12 px-4">
        {/* بخش اصلی فوتر */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {/* ستون اول - درباره ما */}
          <aside className="flex flex-col space-y-4">
            <h6 className="footer-title text-lg font-bold border-b border-base-300 pb-2 mb-2">درباره ما</h6>
            <p className="text-base-content/80 leading-relaxed">
              تیم ما، متشکل از متخصصان تکنولوژی با استعداد و خلاق است که تحت رهبری دکتر منهتن فعالیت می‌کنند. هر عضو تیم با دانش و تجربه خود، به پروژه‌ها ارزش افزوده می‌بخشد و با همکاری و همفکری، راه‌حل‌های نوآورانه و کارآمدی ارائه می‌دهند.
            </p>

          </aside>

          {/* ستون دوم - خدمات */}
          <nav className="flex flex-col space-y-4">
            <h6 className="footer-title text-lg font-bold border-b border-base-300 pb-2 mb-2">خدمات</h6>
            <div className="flex flex-col space-y-2">
              <Link href="https://gcorp.cc" className="link link-hover flex items-center">
                <Globe className="w-4 h-4 mr-2 rtl:ml-2 rtl:mr-0" />
                <span>GCORP LLC</span>
              </Link>
              <Link href="#" className="link link-hover">درباره ژورنا</Link>
              <Link href="#" className="link link-hover">تماس با ما</Link>
              <Link href="#" className="link link-hover">حریم خصوصی</Link>
              <Link href="#" className="link link-hover">شرایط استفاده</Link>
            </div>
          </nav>

          {/* ستون سوم - تماس */}
          <aside className="flex flex-col space-y-4">
            <h6 className="footer-title text-lg font-bold border-b border-base-300 pb-2 mb-2">ارتباط با ما</h6>
           
            <div className="flex flex-col space-y-2 mt-2">
              <a href="mailto:info@journa.ir" className="link link-hover flex items-center">
                <Mail className="w-4 h-4 mr-2 rtl:ml-2 rtl:mr-0" />
                <span>info@journa.ir</span>
              </a>
              <a href="tel:+989123456789" className="link link-hover flex items-center">
                <Phone className="w-4 h-4 mr-2 rtl:ml-2 rtl:mr-0" />
                <span>09370290168</span>
              </a>
              <div className="flex items-center">
                <MapPin className="w-4 h-4 mr-2 rtl:ml-2 rtl:mr-0 flex-shrink-0" />
                <span className="text-sm">شیراز</span>
              </div>
            </div>
          </aside>
        </div>
      </div>
      
      {/* بخش کپی‌رایت */}
      <div className="border-t border-base-300 mt-6">
        <div className="container mx-auto py-4 px-4 flex flex-col md:flex-row justify-between items-center">
          <p className="text-sm text-base-content/70">
            &copy; {new Date().getFullYear()} تمامی حقوق محفوظ است.
          </p>
          <div className="flex space-x-4 rtl:space-x-reverse mt-2 md:mt-0">
            
                <span className="font-bold px-2">Dr Manhattan</span>
                <svg
                width="15"
                height="15"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
                fillRule="evenodd"
                clipRule="evenodd"
                className="fill-current"
              >
                <path d="M22.672 15.226l-2.432.811.841 2.515c.33 1.019-.209 2.127-1.23 2.456-1.15.325-2.148-.321-2.463-1.226l-.84-2.518-5.013 1.677.84 2.517c.391 1.203-.434 2.542-1.831 2.542-.88 0-1.601-.564-1.86-1.314l-.842-2.516-2.431.809c-1.135.328-2.145-.317-2.463-1.229-.329-1.018.211-2.127 1.231-2.456l2.432-.809-1.621-4.823-2.432.808c-1.355.384-2.558-.59-2.558-1.839 0-.817.509-1.582 1.327-1.846l2.433-.809-.842-2.515c-.33-1.02.211-2.129 1.232-2.458 1.02-.329 2.13.209 2.461 1.229l.842 2.515 5.011-1.677-.839-2.517c-.403-1.238.484-2.553 1.843-2.553.819 0 1.585.509 1.85 1.326l.841 2.517 2.431-.81c1.02-.33 2.131.211 2.461 1.229.332 1.018-.21 2.126-1.23 2.456l-2.433.809 1.622 4.823 2.433-.809c1.242-.401 2.557.484 2.557 1.838 0 .819-.51 1.583-1.328 1.847m-8.992-6.428l-5.01 1.675 1.619 4.828 5.011-1.674-1.62-4.829z"></path>
              </svg>
             
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
