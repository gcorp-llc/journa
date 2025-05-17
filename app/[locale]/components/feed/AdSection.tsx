"use client";
export const dynamic = "force-dynamic";
import { useState, useEffect } from "react";
import { useTranslations } from "next-intl";
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";
import "swiper/css/effect-cards";
import Image from "next/image";
import AlertError from "../ui/AlertError";
import AdSkeleton from "../ui/AdSkeleton";
import { motion, AnimatePresence } from "framer-motion";
import { Swiper, SwiperSlide } from "swiper/react";
import { EffectCards, Autoplay, Pagination } from "swiper/modules";

import { AdItem } from "@/types/advertisement";
import { Link } from "@/i18n/navigation";

export default function AdSection({ locale }: { locale: string }) {
  const t = useTranslations("AdSection");
  const [ads, setAds] = useState<AdItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const cacheKey = `ads_${locale}`;
  const cacheTTL = 300000; // 5 دقیقه

  useEffect(() => {
    async function fetchAds() {
      try {
        // چک کردن کش
        const cachedData = localStorage.getItem(cacheKey);
        if (cachedData) {
          const { data, timestamp } = JSON.parse(cachedData);
          if (Date.now() - timestamp < cacheTTL) {
            console.log("Using cached ads:", data);
            setAds(data);
            setLoading(false);
            return;
          }
        }

        const response = await fetch(`/api/ads?locale=${locale}&limit=5`, {
          method: "GET",
          headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "Cache-Control": "no-store",
          },
        });

        if (!response.ok) {
          throw new Error(
            `HTTP error: ${response.status} ${response.statusText}`
          );
        }

        const {
          data,
          success,
          source,
          error: apiError,
        } = await response.json();
        if (!success || !Array.isArray(data)) {
          throw new Error("Invalid data format: Expected an array");
        }

        console.log("Fetched ads:", { source, data });

        // ذخیره در localStorage
        localStorage.setItem(
          cacheKey,
          JSON.stringify({ data, timestamp: Date.now() })
        );

        setAds(data);
        if (apiError) {
          setError(t("error") || `API error: ${apiError}`);
        }
        setLoading(false);
      } catch (err: unknown) {
        const errorMessage =
          err instanceof Error ? err.message : "Unknown error";
        console.error("Error loading ads:", {
          message: errorMessage,
          locale,
        });
        const cachedData = localStorage.getItem(cacheKey);
        if (cachedData) {
          const { data } = JSON.parse(cachedData);
          console.log("Using cached ads on error:", data);
          setAds(data);
          setLoading(false);
        } else {
          setError(t("error") || "Failed to load advertisements");
          setLoading(false);
        }
      }
    }

    if (locale && ["en", "fa", "ar"].includes(locale)) {
      fetchAds();
    } else {
      console.error("Invalid or missing locale:", { locale });
      setError(t("error") || "Invalid locale");
      setLoading(false);
    }
  }, [locale, t, cacheKey]);

  const handleAdClick = async (adId: number) => {
    try {
      await fetch(`/api/ads/${adId}/click`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
      });
    } catch (err) {
      console.error("Error tracking ad click:", err);
    }
  };

  if (loading) {
    return (
      <div className="container mx-auto py-4">
        <AdSkeleton />
      </div>
    );
  }

  if (error && ads.length === 0) {
    return (
      <div className="container mx-auto py-4">
        <AlertError message={error} />
      </div>
    );
  }

  if (ads.length === 0) {
    return (
      <div className="container mx-auto py-4 text-center">
        <p className="text-lg text-gray-500">
          {t("noAds") || "No ads available"}
        </p>
      </div>
    );
  }

  return (
    <section className="container mx-auto py-4 ">
      <h2 className="text-2xl font-bold mb-4 text-center">
        {t("title") || "Advertisements"}
      </h2>

      {/* نمایش زیر هم در دسکتاپ */}
      <div className="hidden md:block">
        <div className="space-y-4">
          {ads.map((ad) => (
            <a
              key={ad.id}
              href={ad.destination_url}
              target="_blank"
              rel="noopener noreferrer"
              className="block"
              onClick={() => handleAdClick(ad.id)}
            >
              {ad.cover ? (
                <Image
                  src={ad.cover}
                  alt={ad.title}
                  width={600}
                  height={150}
                  className="w-full h-auto object-cover rounded-lg"
                  priority={ad.id === 1}
                  placeholder="blur"
                  blurDataURL="/placeholder.png"
                />
              ) : (
                <div className="bg-gray-200 h-48 flex items-center justify-center rounded-lg">
                  <span className="text-gray-500">{ad.title}</span>
                </div>
              )}
            </a>
          ))}
        </div>
      </div>

      {/* اسلایدر در موبایل */}
      <div className="block md:hidden mx-10 mt-10 md:mx-5">
        <Swiper
          effect="cards"
          grabCursor={true}
          autoplay={{ delay: 3000, disableOnInteraction: false }}
          pagination={{ clickable: true }}
          modules={[EffectCards, Autoplay, Pagination]}
          className="mySwiper w-full"
        >
          <AnimatePresence>
            {ads.map((ad) => (
              <SwiperSlide
                key={ad.id}
                className="bg-base-100 p-4 rounded-md border border-base-300"
              >
                <Link
                  href={ad.destination_url}
                  target="_blank"
                  rel="noopener noreferrer"
                  aria-label={`Advertisement: ${ad.title}`}
                  className="block"
                  onClick={() => handleAdClick(ad.id)}
                >
                  <motion.div
                    initial={{
                      opacity: 0,
                      x: locale === "fa" || locale === "ar" ? 20 : -20,
                    }}
                    animate={{ opacity: 1, x: 0 }}
                    exit={{
                      opacity: 0,
                      x: locale === "fa" || locale === "ar" ? 20 : -20,
                    }}
                    transition={{ duration: 0.3, ease: "easeOut" }}
                    className="cursor-pointer hover:shadow-lg transition"
                  >
                    {ad.cover ? (
                      <Image
                        src={ad.cover}
                        alt={ad.title}
                        width={300}
                        height={150}
                        className="w-full h-32 object-cover rounded-md mb-2"
                        loading="lazy"
                        placeholder="blur"
                        blurDataURL="/placeholder.png"
                      />
                    ) : (
                      <div className="bg-gray-200 h-32 flex items-center justify-center rounded-md mb-2">
                        <span className="text-gray-500">{ad.title}</span>
                      </div>
                    )}
                    <p className="text-sm text-base-content">{ad.title}</p>
                    <p className="text-xs text-gray-500 mt-1">{ad.subject}</p>
                  </motion.div>
                </Link>
              </SwiperSlide>
            ))}
          </AnimatePresence>
        </Swiper>
      </div>
    </section>
  );
}
