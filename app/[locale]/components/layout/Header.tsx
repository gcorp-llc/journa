"use client";

import Image from "next/image";
import { useState } from "react";
import { Grip, Search } from "lucide-react";
import LanguageSwitcher from "../ui/LanguageSwitcher";
import Menu from "../feed/Menu";
import { getLangDir } from "rtl-detect";
import { Link } from "@/i18n/navigation";

type Locale = "en" | "fa" | "ar";

const Header = ({ locale }: { locale: Locale }) => {
  const [isSearching, setIsSearching] = useState(false);
  const direction = getLangDir(locale);

  const handleSearchClick = () => {
    setIsSearching(true);
    // Reset after navigation completes
    setTimeout(() => setIsSearching(false), 1000);
  };

  const closeDrawer = () => {
    const drawerCheckbox = document.getElementById(
      "my-drawer"
    ) as HTMLInputElement;
    if (drawerCheckbox) {
      drawerCheckbox.checked = false;
    }
  };

  return (
    <header className={`fixed top-0 left-0 right-0 z-[100] font-vazir ${direction}`}>
      <div className="flex justify-between items-center p-2 bg-amber-500 bg-opacity-95 backdrop-blur-sm rounded-xl shadow-lg m-2">
        <div className="flex items-center gap-4">
          <div className="drawer drawer-start">
            <input id="my-drawer" type="checkbox" className="drawer-toggle" />
            <div className="drawer-content">
              <label
                htmlFor="my-drawer"
                className="btn btn-ghost btn-circle hover:bg-amber-600"
              >
                <Grip className="w-6 h-6 text-white" />
              </label>
            </div>
            <Menu onLinkClick={closeDrawer} />
          </div>
        </div>

        <div className="flex items-center gap-1 mg:gap-4">
          <Link
            href="/search"
            onClick={handleSearchClick}
            className="btn btn-ghost btn-circle hover:bg-amber-600"
          >
            <div className="w-6 h-6 flex items-center justify-center">
              {isSearching ? (
                <span className="loading loading-spinner loading-sm text-white"></span>
              ) : (
                <Search className="w-6 h-6 text-white" />
              )}
            </div>
          </Link>
          <LanguageSwitcher />
          <Link href="/" className="w-9 hover:opacity-90 transition-opacity">
            <Image
              src="/favicon.png"
              alt="Journa News"
              width={50}
              height={50}
              className="rounded-full"
            />
          </Link>
        </div>
      </div>
    </header>
  );
};

export default Header;
