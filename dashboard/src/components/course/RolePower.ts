import moment from "moment";
import {
  TCourseJoinMode,
  TCourseMemberAction,
  TCourseMemberStatus,
} from "../api/Course";

export interface IAction {
  mode: TCourseJoinMode[];
  status: TCourseMemberStatus;
  signUp?: TCourseMemberAction[];
  before: TCourseMemberAction[];
  duration: TCourseMemberAction[];
  after: TCourseMemberAction[];
}

export const getStudentActionsByStatus = (
  status?: TCourseMemberStatus,
  mode?: TCourseJoinMode,
  startAt?: string,
  endAt?: string,
  signUpStartAt?: string,
  signUpEndAt?: string
): TCourseMemberAction[] | undefined => {
  const output = getActionsByStatus(
    studentData,
    status,
    mode,
    startAt,
    endAt,
    signUpStartAt,
    signUpEndAt
  );
  console.log("getStudentActionsByStatus", output);
  return output;
};
const getActionsByStatus = (
  data: IAction[],
  status?: TCourseMemberStatus,
  mode?: TCourseJoinMode,
  startAt?: string,
  endAt?: string,
  signUpStartAt?: string,
  signUpEndAt?: string
): TCourseMemberAction[] | undefined => {
  console.debug("getActionsByStatus start");
  if (!startAt || !endAt || !mode || !status) {
    return undefined;
  }
  const inSignUp = moment().isBetween(
    moment(signUpStartAt),
    moment(signUpEndAt)
  );
  const actions = data.find((value) => {
    if (value.mode.includes(mode) && value.status === status) {
      if (inSignUp) {
        if (value.signUp && value.signUp.length > 0) {
          console.debug("getActionsByStatus got it", value.signUp);
          return true;
        }
      }
      if (moment().isBefore(moment(startAt))) {
        if (value.before && value.before.length > 0) {
          return true;
        }
      }
      if (moment().isBefore(moment(endAt))) {
        if (value.duration && value.duration.length > 0) {
          return true;
        }
      }
      if (value.after && value.after.length > 0) {
        return true;
      }
    }
    return false;
  });

  if (actions) {
    if (inSignUp && actions.signUp && actions.signUp.length > 0) {
      return actions.signUp;
    } else if (moment().isBefore(moment(startAt))) {
      return actions.before;
    } else if (moment().isBefore(moment(endAt))) {
      return actions.duration;
    } else {
      return actions.after;
    }
  } else {
    return undefined;
  }
};

export const test = (
  data: IAction[],
  action: TCourseMemberAction,
  startAt?: string,
  endAt?: string,
  mode?: TCourseJoinMode,
  status?: TCourseMemberStatus,
  signUpStartAt?: string,
  signUpEndAt?: string
): boolean => {
  if (!startAt || !endAt || !mode || !status) {
    return false;
  }
  const canDo = getActionsByStatus(
    data,
    status,
    mode,
    startAt,
    endAt,
    signUpStartAt,
    signUpEndAt
  )?.includes(action);

  if (canDo) {
    return true;
  } else {
    return false;
  }
};

export const managerCanDo = (
  action: TCourseMemberAction,
  startAt?: string,
  endAt?: string,
  mode?: TCourseJoinMode,
  status?: TCourseMemberStatus,
  signUpStartAt?: string,
  signUpEndAt?: string
): boolean => {
  if (!startAt || !endAt || !mode || !status) {
    return false;
  }

  return test(
    managerData,
    action,
    startAt,
    endAt,
    mode,
    status,
    signUpStartAt,
    signUpEndAt
  );
};

export const studentCanDo = (
  action: TCourseMemberAction,
  startAt?: string,
  endAt?: string,
  mode?: TCourseJoinMode,
  status?: TCourseMemberStatus,
  signUpStartAt?: string,
  signUpEndAt?: string
): boolean => {
  if (!startAt || !endAt || !mode || !status) {
    return false;
  }

  return test(
    studentData,
    action,
    startAt,
    endAt,
    mode,
    status,
    signUpStartAt,
    signUpEndAt
  );
};

interface IStatusColor {
  status: TCourseMemberStatus;
  color: string;
}
export const getStatusColor = (status?: TCourseMemberStatus): string => {
  let color = "unset";
  const setting: IStatusColor[] = [
    { status: "applied", color: "blue" },
    { status: "invited", color: "blue" },
    { status: "accepted", color: "green" },
    { status: "agreed", color: "green" },
    { status: "rejected", color: "orange" },
    { status: "disagreed", color: "red" },
    { status: "left", color: "red" },
    { status: "blocked", color: "orange" },
  ];
  const CourseStatusColor = setting.find((value) => value.status === status);

  if (CourseStatusColor) {
    color = CourseStatusColor.color;
  }
  return color;
};

const studentData: IAction[] = [
  {
    mode: ["open"],
    status: "none",
    signUp: ["join"],
    before: [],
    duration: [],
    after: [],
  },
  {
    mode: ["open"],
    status: "applied",
    before: ["leave"],
    duration: ["leave"],
    after: [],
  },
  {
    mode: ["open"],
    status: "joined",
    before: ["leave"],
    duration: ["leave"],
    after: [],
  },
  {
    mode: ["open"],
    status: "left",
    signUp: ["join"],
    before: [],
    duration: ["join"],
    after: [],
  },
  {
    mode: ["open"],
    status: "invited",
    signUp: ["agree", "disagree"],
    before: ["agree", "disagree"],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "none",
    signUp: ["apply"],
    before: [],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "invited",
    signUp: ["agree", "disagree"],
    before: ["agree", "disagree"],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "revoked",
    signUp: ["apply"],
    before: [],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "accepted",
    before: ["leave"],
    duration: ["leave"],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "rejected",
    before: [],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "blocked",
    before: [],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "applied",
    before: ["cancel"],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "canceled",
    signUp: ["apply"],
    before: [],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "agreed",
    before: ["leave"],
    duration: ["leave"],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "disagreed",
    signUp: ["apply"],
    before: [],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "left",
    before: ["apply"],
    duration: [],
    after: [],
  },
];

const managerData: IAction[] = [
  {
    mode: ["manual", "invite"],
    status: "none",
    before: [],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "invited",
    before: ["revoke"],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "revoked",
    before: [],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "canceled",
    signUp: [],
    before: [],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "accepted",
    before: [],
    duration: ["block"],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "rejected",
    before: [],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "blocked",
    before: [],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "applied",
    before: ["accept", "reject"],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "agreed",
    before: [],
    duration: ["block"],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "disagreed",
    before: [],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "left",
    before: [],
    duration: [],
    after: [],
  },
];
