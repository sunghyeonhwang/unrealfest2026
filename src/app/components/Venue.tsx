import React from "react";
import { MapPin, Train, Car, Navigation } from "lucide-react";

export const Venue = () => {
  return (
    <section id="venue" className="py-24 bg-[#09090b] relative border-t border-white/5">
      <div className="max-w-7xl mx-auto px-6">
        <div className="mb-16">
          <h2 className="text-3xl md:text-5xl font-bold text-white mb-4 tracking-tight">행사장 안내</h2>
          <p className="text-[#90a1b9]">
            행사장 위치와 체크인, 교통 정보를 확인해 보세요.
          </p>
        </div>

        <div className="grid lg:grid-cols-2 gap-6">
          {/* 좌측: 지도 */}
          <div className="relative overflow-hidden h-[500px] lg:h-auto">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3165.5!2d127.0574461!3d37.5129292!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x357ca46beba84f0d%3A0xa07ae6d7a2a200e7!2z7Juo7Iqk7YuEIOyEnOyauCDtjIzrpbTrgpjsiqQ!5e0!3m2!1sko!2skr!4v1700000000000!5m2!1sko!2skr&maptype=roadmap"
              className="w-full h-full min-h-[500px]"
              style={{ border: 0, filter: "invert(90%) hue-rotate(180deg) brightness(0.9) contrast(1.1)" }}
              allowFullScreen
              loading="lazy"
              referrerPolicy="no-referrer-when-downgrade"
              title="웨스틴 서울 파르나스 지도"
            />
            {/* 좌상단 지도에서 열기 링크 */}
            <a
              href="https://www.google.com/maps/place/%EC%9B%A8%EC%8A%A4%ED%8B%B4+%EC%84%9C%EC%9A%B8+%ED%8C%8C%EB%A5%B4%EB%82%98%EC%8A%A4/data=!4m13!1m2!2m1!1z7Juo7Iqk7Yu07KGw7ISg7ISc7Jq47YyM66W064KY7Iqk!3m9!1s0x357ca46beba84f0d:0xa07ae6d7a2a200e7!5m2!4m1!1i2!8m2!3d37.5129292!4d127.0574461!15sCiHsm6jsiqTti7TsobDshKDshJzsmrjtjIzrpbTrgpjsiqWSAQVob3RlbOABAA!16s%2Fg%2F11yhvkgm6_?entry=ttu"
              target="_blank"
              rel="noopener noreferrer"
              className="absolute top-4 left-4 text-[#00C1D5] text-sm font-medium flex items-center gap-1 hover:underline z-20"
            >
              지도에서 열기
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            {/* 하단 정보 오버레이 */}
            <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent z-10 p-6 pt-16">
              <div className="flex justify-between items-end">
                <div>
                  <h3 className="text-2xl font-bold text-white mb-1">웨스틴 서울 파르나스</h3>
                  <p className="text-slate-300">서울특별시 강남구 테헤란로 606</p>
                </div>
                <a
                  href="https://www.google.com/maps/place/%EC%9B%A8%EC%8A%A4%ED%8B%B4+%EC%84%9C%EC%9A%B8+%ED%8C%8C%EB%A5%B4%EB%82%98%EC%8A%A4/data=!4m13!1m2!2m1!1z7Juo7Iqk7Yu07KGw7ISg7ISc7Jq47YyM66W064KY7Iqk!3m9!1s0x357ca46beba84f0d:0xa07ae6d7a2a200e7!5m2!4m1!1i2!8m2!3d37.5129292!4d127.0574461!15sCiHsm6jsiqTti7TsobDshKDshJzsmrjtjIzrpbTrgpjsiqWSAQVob3RlbOABAA!16s%2Fg%2F11yhvkgm6_?entry=ttu"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="bg-white text-black px-4 py-2 font-bold text-sm flex items-center gap-2 hover:bg-neutral-100 transition-colors"
                >
                  <Navigation className="w-4 h-4" />
                  지도 열기
                </a>
              </div>
            </div>
          </div>

          {/* 우측: 안내 정보 */}
          <div className="bg-[#0e0f14] flex flex-col divide-y divide-[#27272a]">
            <div className="p-8 flex gap-5">
              <div className="w-10 h-10 rounded-full bg-[rgba(0,193,213,0.1)] border border-[rgba(0,193,213,0.2)] flex items-center justify-center flex-shrink-0 mt-1">
                <MapPin className="w-5 h-5 text-[#00C1D5]" />
              </div>
              <div>
                <h4 className="text-lg font-bold text-white mb-2">행사장 체크인</h4>
                <p className="text-sm text-[#a1a1aa] leading-relaxed">
                  지하 1층 하모니 볼룸 앞 데스크에서 QR 코드 확인 후 명찰을 수령할 수 있습니다. 체크인은 매일 오전 9시부터 시작됩니다.
                </p>
              </div>
            </div>

            <div className="p-8 flex gap-5">
              <div className="w-10 h-10 rounded-full bg-[rgba(0,193,213,0.1)] border border-[rgba(0,193,213,0.2)] flex items-center justify-center flex-shrink-0 mt-1">
                <Train className="w-5 h-5 text-[#00C1D5]" />
              </div>
              <div>
                <h4 className="text-lg font-bold text-white mb-2">대중교통</h4>
                <p className="text-sm text-[#a1a1aa] leading-relaxed">
                  <strong className="text-white">지하철:</strong> 2호선 삼성역 5번 출구, 9호선 봉은사역 7번 출구<br />
                  <strong className="text-white">버스:</strong> 봉은사, 코엑스 북문 정류장 하차
                </p>
              </div>
            </div>

            <div className="p-8 flex gap-5">
              <div className="w-10 h-10 rounded-full bg-[rgba(0,193,213,0.1)] border border-[rgba(0,193,213,0.2)] flex items-center justify-center flex-shrink-0 mt-1">
                <Car className="w-5 h-5 text-[#00C1D5]" />
              </div>
              <div>
                <h4 className="text-lg font-bold text-white mb-2">주차 안내</h4>
                <p className="text-sm text-[#a1a1aa] leading-relaxed">
                  행사 참가자에게는 무료 주차권이 제공됩니다. 명찰 수령 시 데스크에서 차량 번호를 등록해 주세요. 다만 주차 공간이 제한될 수 있으니 가급적 대중교통 이용을 권장드립니다.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};
