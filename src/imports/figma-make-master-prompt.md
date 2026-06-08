# Figma Make Master Prompt

## Usage

- Use this prompt when requesting the full redesign concept in Figma Make.
- Recommended one-file workflow: attach this file only.
- Recommended high-fidelity workflow: attach this file and `../unrealfest2026-site-architecture.md`
- The file paths above are for your local organization only. Figma Make will not automatically open local paths written inside the document, so any extra file must be attached manually.

## Local Reference Paths

- Master prompt: `./figma-make-master-prompt.md`
- Planning document: `../unrealfest2026-site-architecture.md`
- Home-focused prompt: `./figma-make-home-prompt.md`
- Agenda-focused prompt: `./figma-make-agenda-prompt.md`
- Visual-heavy prompt: `./figma-make-visual-heavy-prompt.md`

## Embedded Brief

Use this section so the file works even when attached alone.

### Project Goal

- Rebuild the current event site into a clearer premium one-page landing for `Unreal Fest Seoul 2026`
- Improve `Agenda` discoverability
- Keep the whole experience on one page
- Show session detail only as a modal overlay

### Fixed Event Information

- Project name: `언리얼 페스트 서울 2026 공식 사이트`
- Event dates: `2026년 8월 20일(목) ~ 21일(금)`
- Venue: `웨스틴 조선 서울 파르나스`
- Format: `오프라인 중심 + 일부 세션 온라인 방송`
- Host: `에픽게임즈 코리아`
- Operator: `에픽라운지 (주식회사 그리프)`

### Information Architecture Summary

- The result must be a single long-scroll landing page
- Overview, Tracks, Agenda, Register, Venue, Sponsors, Events, and FAQ are sections on the same page
- Session Detail is the only overlay and must open as a modal
- Registration Status should appear only as a CTA or utility link, not as a separate designed page in the output

### Navigation Requirements

- Header nav: Overview, Tracks, Agenda, Venue, Sponsors, FAQ
- Right-side CTA: Register, Registration Status
- Footer must separate event navigation from Epic Lounge global links

### One-Page Section Direction

- Build a single page with these major sections:
  - Hero
  - Overview
  - Tracks
  - Agenda
  - Register
  - Venue
  - Sponsors
  - Events
  - FAQ
  - Footer
- Keep information hierarchy strong and easy to scan
- Hero should be powerful, but not poster-only or empty of information

### Agenda-Specific Direction

- Make Agenda the strongest UX surface within the one-page layout
- Include: Day 1 / Day 2 tabs, track filters, level filters, optional format filters, summary bar, hybrid card+timetable layout
- Every session card should show time, title, speaker, track, level, and a clear action
- The clear action should open a session detail modal, not navigate to another page
- Make scanning and comparison easy before visual flair

### Modal Rule

- Session Detail is the only content that should appear as a modal / overlay
- Do not create separate pages for session detail
- Do not create separate pages for register, venue, sponsors, or FAQ

### Visual Direction Override

- If a stronger concept is needed, push toward premium tech conference, cinematic staging, volumetric light, high contrast, real-time 3D energy
- Use black / graphite / deep slate base with electric cyan / ice blue accents
- Warm highlight colors should be limited to CTA emphasis only
- Avoid generic AI SaaS landing patterns, purple cyberpunk cliches, and excessive glassmorphism

### Output Scope

- Desktop 1440px: one-page main screen
- Desktop 1440px: session detail modal state
- Mobile 390px: one-page main screen
- High-fidelity mockup
- Auto Layout-based components
- Reusable buttons, tabs, filter chips, session cards, accordion, CTA banner
- Include short design rationale per screen

## Prompt

```text
첨부한 사이트맵/기획서를 source of truth로 삼아 Unreal Fest Seoul 2026 웹사이트 시안을 만들어줘.

목표:
기존 사이트를, 사용자 과업 중심의 명확한 프리미엄 원페이지 행사 랜딩으로 재구성한다.
가장 중요한 개선 포인트는 다음 3가지다.
1. Agenda 탐색성을 크게 높인다.
2. 세션 상세는 페이지가 아니라 modal 경험으로 만든다.
3. 사용자가 한 페이지 안에서 행사 이해와 등록 결정을 끝낼 수 있게 만든다.

절대 중요:
- 결과물은 separate pages가 아니라 `one-page landing page`여야 한다.
- Overview, Tracks, Agenda, Register, Venue, Sponsors, Events, FAQ는 모두 같은 페이지 안의 섹션이어야 한다.
- Session Detail만 유일하게 modal / overlay로 보여줘야 한다.
- Registration Status는 별도 페이지 시안으로 만들지 말고, 헤더 또는 등록 섹션의 보조 CTA로만 표현해라.

우선 제작할 화면:
- Desktop 1440px: one-page main screen 1종
- Desktop 1440px: session detail modal state 1종
- Mobile 390px: one-page main screen 1종
- 모든 화면은 하나의 일관된 디자인 시스템 안에서 설계해라.

고정 이벤트 정보:
- 프로젝트명: 언리얼 페스트 서울 2026 공식 사이트
- 일정: 2026년 8월 20일(목) ~ 21일(금)
- 장소: 웨스틴 조선 서울 파르나스
- 형식: 오프라인 중심 + 일부 세션 온라인 방송
- 주최: 에픽게임즈 코리아
- 운영: 에픽라운지 (주식회사 그리프)

디자인 방향:
- 분위기: premium tech conference, cinematic, real-time 3D, confident, polished
- 절대 피할 것: generic startup landing page, 보라색 위주의 흔한 AI 스타일, 과한 glassmorphism, 정보 없는 hero-only 구성
- 컬러: black / graphite / deep slate 기반, electric cyan / ice blue 포인트, 제한적인 warm CTA highlight
- 타이포: 강한 헤드라인, 선명한 정보 계층, 한국어 중심 UI, 영어는 Agenda / Session / Speakers 같은 보조 라벨에만 제한적으로 사용
- 레이아웃: 넓은 여백, 강한 그리드, sticky header, 명확한 CTA 위계, 카드 기반 모듈 구성
- 그래픽 무드: abstract 3D lighting, volumetric glow, real-time rendering 느낌. 불필요한 스크린샷 콜라주는 피한다.

핵심 UX 요구:
- 이 결과물은 Home, Agenda, Register, Venue를 따로 나눈 멀티페이지 세트가 아니라 하나의 긴 스크롤 랜딩이어야 한다.
- Hero 섹션:
  행사명, 핵심 메시지, 날짜/장소, Primary CTA, Secondary CTA 포함
- Overview 섹션:
  행사 소개, 일정, 장소, 운영 방식, 참석 가치 포함
- Tracks 섹션:
  게임, 미디어 & 엔터테인먼트, 산업 / 시뮬레이션 트랙 소개
- Agenda 섹션:
  Day 1 / Day 2 tabs, track filters, level filters, summary bar, card + timetable hybrid layout
- Session Detail Modal:
  제목, 날짜/시간, 트랙, 난이도, 발표자, 세션 소개, 세션 목차, 권장 대상, 캘린더 추가, Register CTA
- Register 섹션:
  ticket options, 참가 방식, 정책 요약, Register CTA, Registration Status 링크
- Venue 섹션:
  장소, 체크인 안내, 오시는 길
- Sponsors 섹션:
  스폰서 로고와 하이라이트
- Events 섹션:
  부대 이벤트 또는 커뮤니티 요소
- FAQ 섹션:
  accordion 구조, 등록/결제/입장/온라인 시청/환불/참가 확인증 카테고리 포함

내비게이션 요구:
- Header nav: Overview, Tracks, Agenda, Venue, Sponsors, FAQ
- 우측 고정 CTA: Register, Registration Status
- Footer는 행사 메뉴와 Epic Lounge 글로벌 링크 영역을 분리해라.

출력 요구:
- high-fidelity mockup으로 만들어라. 단순 와이어프레임 말고 실제 시안 수준이어야 한다.
- Auto Layout 기반으로 설계하고, 재사용 가능한 버튼, 탭, 필터칩, 세션카드, FAQ 아코디언, CTA 배너 스타일을 포함해라.
- 화면 간 일관된 spacing, type scale, color tokens, card system을 보여줘.
- 실제 웹으로 구현 가능한 수준의 구조와 컴포넌트 체계를 유지해라.
- separate pages를 만들지 말고, 반드시 one-page 구조를 유지해라.
- Session Detail만 modal로 만들어라.
- 각 화면 하단 또는 별도 영역에 design rationale을 3~5줄 정도 짧게 정리해라.

전체 결과물은 “Unreal Fest Seoul 2026를 위한 프리미엄 원페이지 행사 웹사이트 리디자인 시안”처럼 보여야 한다.
```
