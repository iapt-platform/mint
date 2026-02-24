export type GreetingPeriod = "lateNight" | "morning" | "afternoon" | "evening";

export type Greeting = {
  zh: string;   // 主标题
  sub: string;  // 副标题
  en: string;   // 顶部小标签
};

const greetings: Record<GreetingPeriod, Greeting[]> = {
  // 深夜 0–4 时
  lateNight: [
    { zh: "深夜仍在用功", sub: "精进不懈，是修行者的庄严。", en: "Still practicing at midnight" },
    { zh: "夜深心不散", sub: "于寂静中，观照诸法生灭。", en: "A quiet mind in the deep night" },
    { zh: "夜半修观", sub: "睡眠盖轻，正念正知常相随。", en: "Late night contemplation" },
    { zh: "深夜清醒", sub: "不放逸者，如明灯照暗室。", en: "Wakeful in the stillness" },
    { zh: "万籁俱寂", sub: "此刻最宜止观，心随息而定。", en: "Silence and stillness await" },
    { zh: "夜深精进", sub: "佛陀亦于夜分证悟，愿此夜有所得。", en: "Diligent through the night" },
    { zh: "不眠者的清明", sub: "以法为灯，以戒为足，行于黑暗中。", en: "Clarity in the small hours" },
    { zh: "深夜共修", sub: "持续用功，善根日益增长。", en: "Continued effort, continued growth" },
    { zh: "静夜观心", sub: "心若澄澈，烦恼无处可藏。", en: "Observing the mind at night" },
    { zh: "夜分不放逸", sub: "诸天赞叹不放逸者，愿你精进。", en: "Heedful through the night" },
  ],

  // 早晨 5–11 时
  morning: [
    { zh: "清晨吉祥", sub: "以清净心迎接新的一天。", en: "Auspicious morning" },
    { zh: "晨起修行", sub: "日出之前发愿，日落之前无悔。", en: "Morning practice begins" },
    { zh: "早安，精进者", sub: "晨露未晞，正念已起，善哉。", en: "Good morning, diligent one" },
    { zh: "朝阳初升", sub: "愿今日所学，回向一切众生。", en: "As the sun rises" },
    { zh: "晨课吉时", sub: "诵经、禅修，从此刻开始。", en: "The auspicious hour of morning study" },
    { zh: "新的一天", sub: "昨日已过，今日因缘具足，莫空过。", en: "A new day, a new opportunity" },
    { zh: "晨光中正念", sub: "正念正知，于行住坐卧中修习。", en: "Mindfulness in the morning light" },
    { zh: "清晨发心", sub: "愿以此身此心，奉行佛法。", en: "Setting intention at dawn" },
    { zh: "破晓精进", sub: "黎明属于不放逸者，善加利用。", en: "Dawn belongs to the diligent" },
    { zh: "晨起观身", sub: "于清醒之初，观此身如实知见。", en: "Observing the body at first light" },
  ],

  // 下午 12–17 时
  afternoon: [
    { zh: "午后仍精进", sub: "莫因日中而懈怠，法义常相续。", en: "Diligent through the afternoon" },
    { zh: "日中已过", sub: "上午所学，午后深化，善加思惟。", en: "The day continues" },
    { zh: "午后禅思", sub: "食后少动，正是思择法义之时。", en: "Afternoon reflection" },
    { zh: "下午吉祥", sub: "以闻思修，令善法增长。", en: "Auspicious afternoon" },
    { zh: "持续用功", sub: "修行无假期，每一刻皆是道场。", en: "Continuing the practice" },
    { zh: "日午不放逸", sub: "懈怠是修行的障碍，精进是解脱的资粮。", en: "Heedful in the afternoon" },
    { zh: "午后清明", sub: "心平气和，于法义中深入思择。", en: "Clear-minded this afternoon" },
    { zh: "日照当下", sub: "此刻因缘，是过去善业之果，善加珍惜。", en: "Present in this moment" },
    { zh: "下午共学", sub: "独学而无友，则孤陋而寡闻，善哉同行。", en: "Learning together this afternoon" },
    { zh: "法喜充满", sub: "以法为食，以定为饮，身心安乐。", en: "Nourished by the Dhamma" },
  ],

  // 傍晚 18–23 时
  evening: [
    { zh: "晚安，善修者", sub: "一日将尽，回顾今日，有否精进？", en: "Good evening, practitioner" },
    { zh: "暮色修观", sub: "夜幕降临，正是内观的好时机。", en: "Evening contemplation" },
    { zh: "日暮不放逸", sub: "一日将终，善法若有增长，甚为值得。", en: "Heedful as evening falls" },
    { zh: "傍晚回向", sub: "愿今日修学功德，回向法界一切众生。", en: "Evening dedication of merit" },
    { zh: "夜课时分", sub: "灯火初上，诵经坐禅，法喜油然而生。", en: "The evening study session" },
    { zh: "静心入夜", sub: "放下日间纷扰，于法中安住。", en: "Settling into the evening" },
    { zh: "晚间思择", sub: "以智慧观照今日身口意，有所进益否？", en: "Evening reflection" },
    { zh: "暮色中精进", sub: "修行者无论晨昏，正念常相续。", en: "Diligent as dusk falls" },
    { zh: "夜灯长明", sub: "以法为灯，照亮前行之路。", en: "The lamp of Dhamma burns bright" },
    { zh: "晚上吉祥", sub: "愿今夜禅修安定，明日再接再厉。", en: "An auspicious evening" },
  ],
};

function getPeriod(hour: number): GreetingPeriod {
  if (hour < 5) return "lateNight";
  if (hour < 12) return "morning";
  if (hour < 18) return "afternoon";
  return "evening";
}

function getRandom<T>(arr: T[]): T {
  return arr[Math.floor(Math.random() * arr.length)];
}

export function getGreeting(): Greeting {
  const hour = new Date().getHours();
  const period = getPeriod(hour);
  return getRandom(greetings[period]);
}
