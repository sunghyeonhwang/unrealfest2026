# Unreal Fest 2026 Seoul — Design System

## 1. Color System

### Brand Colors
| Role | Hex | Usage |
|------|-----|-------|
| **Key Color** | `#00C1D5` | CTA 버튼, 링크 호버, 체크박스, 포커스 보더 |
| **Shadow Color** | `#004F59` | CTA 버튼 호버 |
| **Highlight Color** | `#9ADBE8` | 다크모드 텍스트 액센트, 라이트 배경 tint (`/10`) |

### Track Colors
| Track | Main | Sub |
|-------|------|-----|
| 게임 - 프로그래밍 | `#307FE2` | `#002855` |
| 게임 - 아트 | `#FF8F1C` | `#FECB8B` |
| 미디어 & 엔터테인먼트 | `#FA4616` | `#FF8674` |
| 산업 & 시뮬레이션 | `#DD0AB2` | `#DD9CDF` |

### Light Theme
- 배경: `white` / `neutral-50`
- 텍스트: `black` / `black/60` / `black/40`
- 보더: `black/10` / `black/15`
- 카드: `white`

### Dark Theme
- 배경: `#09090b` / `#0B0C10` / `#111115`
- 텍스트: `white` / `slate-400` / `slate-500`
- 보더: `white/5` / `white/10`
- 카드: `#111115`

### 고정 다크 영역
- GNB Header: 라이트/다크 무관 항상 다크
- Hero 섹션: 항상 다크 (영상 배경)
- Footer: 항상 `bg-black`

---

## 2. Shape System — 5각형 Clip-Path

로고의 각진 형태에서 파생된 디자인 언어. `clip-path: polygon()`으로 모서리를 잘라 5각형을 만듦.

### 우측 상단 접힘
```css
clip-path: polygon(0 0, calc(100% - 24px) 0, 100% 24px, 100% 100%, 0 100%);
```
```
┌──────────╲
│            │
│            │
└────────────┘
```
**적용**: 필터 박스, 키노트 카드(그리드), 트랙 카드, FAQ 문의 박스, 오프라인 티켓 카드

### 우측 하단 접힘
```css
clip-path: polygon(0 0, 100% 0, 100% calc(100% - 20px), calc(100% - 20px) 100%, 0 100%);
```
```
┌────────────┐
│            │
│          ╱ │
└────────╱───┘
```
**적용**: 아젠다 그리드 세션 카드, 온라인 티켓 카드, CTA 버튼, 세션 모달

### 크기 가이드
| 요소 | 접힘 크기 |
|------|----------|
| 대형 카드/모달 | `28px` |
| 섹션 박스/카드 | `24px` / `20px` |
| 그리드 세션 카드 | `16px` |
| 버튼 | `12px` / `10px` |

### 교차 패턴
리스트뷰에서 짝수/홀수 인덱스에 따라 우상단/우하단 교차 적용:
```tsx
const clip = idx % 2 === 0 ? clipTopRight : clipBottomRight;
```

---

## 3. Typography

### 폰트
- **기본**: system-ui, sans-serif
- **Hero 서브타이틀**: Inter Tight 800 (Google Fonts CDN)

### 텍스트 크기 체계
| 용도 | 클래스 |
|------|--------|
| Hero 제목 (로고 이미지) | SVG `max-w-2xl` |
| 섹션 제목 | `text-3xl md:text-5xl font-bold` |
| 카드 제목 | `text-xl ~ text-2xl font-bold` |
| 본문 | `text-base ~ text-lg` |
| 서브 텍스트 | `text-sm` |
| 캡션/메타 | `text-xs` |

### 전역 설정
```css
* { word-break: keep-all; }
```

---

## 4. Layout

### 그리드 시스템
- 최대 폭: `max-w-7xl` (1280px)
- 패딩: `px-6`
- 섹션 간격: `py-24`

### 카드 레이아웃
| 섹션 | 그리드 |
|------|--------|
| 트랙 | `grid-cols-2 lg:grid-cols-4` |
| 아젠다 그리드 | `grid-cols-4` (트랙별 컬럼) |
| 티켓 | `grid-cols-2` |
| 스폰서 Gold | `grid-cols-2` |
| 스폰서 Silver | `grid-cols-2 md:grid-cols-4` |
| 이벤트 | 1행 전체폭 + 2행/3행 `grid-cols-2` |
| 등록 폼 | `grid-cols-12` (폼 7~8 + 사이드바 4~5) |

### Sticky 사이드바 (등록 페이지)
```tsx
<div className="self-start sticky top-28">
```

---

## 5. Components

### CTA 버튼
```tsx
className="bg-[#00C1D5] hover:bg-[#004F59] text-white font-bold"
style={{ clipPath: "polygon(0 0, 100% 0, 100% calc(100% - 12px), calc(100% - 12px) 100%, 0 100%)" }}
```
- 보더 없음
- 우하단 12px 접힘
- GNB 버튼은 10px 접힘

### 필터 (아젠다)
- 트랙 필터: 선택 안 된 트랙 컬럼 `opacity-30` (숨기지 않음)
- 난이도 필터: 매칭 안 되는 카드 `opacity-30`
- `rounded-full` pill 형태 유지

### 입력 필드 (등록 페이지)
```tsx
"border-0 border-b-2 border-transparent focus:border-[#00C1D5] focus:ring-0 rounded-none"
```
- 보더 없음, 포커스 시 하단 라인만 표시
- 배경: `bg-neutral-50 dark:bg-[#1A1B23]`

### 스폰서 로고
```tsx
<img className="dark:invert" />
```
- 다크모드에서 `invert` 필터로 색상 반전
- opacity 100%

### 배경 영상
```tsx
<video autoPlay loop muted playsInline className="object-cover object-bottom" />
```
- Hero: 전체 배경, `object-position: bottom`
- 트랙 카드: 각 트랙별 영상, `opacity-20 dark:opacity-50`

---

## 6. 영상 에셋

| 파일 | 용도 | 크기 |
|------|------|------|
| `AmbientLoop_WIP.mp4` | Hero 배경 | 45MB |
| `AAAGames-Fall2025-WebBanner_1080p30-H265-5Mbps.mp4` | 게임-프로그래밍 트랙 | 27MB |
| `unreal-engine-animation-reel.mp4` | 게임-아트 트랙 | 10MB |
| `film-and-tv-hero.mp4` | 미디어&엔터 트랙 | 9.3MB |
| `automotive-and-transport-hero.mp4` | 산업&시뮬 트랙 | 16MB |

---

## 7. 아이콘 사용 원칙

- 입력 필드: 아이콘 없음
- 트랙 카드: 아이콘 없음
- 최소한의 lucide-react 아이콘만 사용 (ArrowRight, Filter, LayoutGrid 등)
- SNS: 유튜브(lucide), 네이버/카카오(인라인 SVG)

---

## 8. 반응형

| 브레이크포인트 | 용도 |
|--------------|------|
| `sm` (640px) | 버튼 가로 배치 |
| `md` (768px) | 2열 그리드 |
| `lg` (1024px) | 4열 그리드, 사이드바 표시 |
| `xl` (1280px) | max-w-7xl 도달 |

---

## 9. 빌드 & 배포

```bash
# 빌드
npm run build

# 배포 (SFTP)
sshpass -p $FTP_PASS sftp -P 22 $FTP_USER@$FTP_HOST <<EOF
cd www/v3/preview_2026
put dist/index.html
quit
EOF
```

- `vite-plugin-singlefile`로 CSS/JS 인라인
- 영상/SVG는 별도 파일로 배포
- `createHashRouter` 사용 (file:// 호환)
