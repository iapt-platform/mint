import moment from "moment";
import {
  TCourseJoinMode,
  TCourseMemberAction,
  TCourseMemberStatus,
} from "../api/Course";

export interface IAction {
  mode: TCourseJoinMode[];
  status: TCourseMemberStatus;
  before: TCourseMemberAction[];
  duration: TCourseMemberAction[];
  after: TCourseMemberAction[];
}

export const getStudentActionsByStatus = (
  status?: TCourseMemberStatus,
  mode?: TCourseJoinMode,
  startAt?: string,
  endAt?: string
): TCourseMemberAction[] | undefined => {
  const output = getActionsByStatus(studentData, status, mode, startAt, endAt);
  return output;
};
const getActionsByStatus = (
  data: IAction[],
  status?: TCourseMemberStatus,
  mode?: TCourseJoinMode,
  startAt?: string,
  endAt?: string
): TCourseMemberAction[] | undefined => {
  if (!startAt || !endAt || !mode || !status) {
    return undefined;
  }
  const actions = data.find((value) => {
    if (value.mode.includes(mode) && value.status === status) {
      if (moment().isBefore(moment(startAt))) {
        if (value.before && value.before.length > 0) {
          return true;
        }
      } else if (moment().isBefore(moment(endAt))) {
        if (value.duration && value.duration.length > 0) {
          return true;
        }
      } else {
        if (value.after && value.after.length > 0) {
          return true;
        }
      }
    }
    return undefined;
  });

  if (actions) {
    if (moment().isBefore(moment(startAt))) {
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
  status?: TCourseMemberStatus
): boolean => {
  if (!startAt || !endAt || !mode || !status) {
    return false;
  }
  const canDo = getActionsByStatus(
    data,
    status,
    mode,
    startAt,
    endAt
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
  status?: TCourseMemberStatus
): boolean => {
  if (!startAt || !endAt || !mode || !status) {
    return false;
  }

  return test(managerData, action, startAt, endAt, mode, status);
};

export const studentCanDo = (
  action: TCourseMemberAction,
  startAt?: string,
  endAt?: string,
  mode?: TCourseJoinMode,
  status?: TCourseMemberStatus
): boolean => {
  if (!startAt || !endAt || !mode || !status) {
    return false;
  }

  return test(studentData, action, startAt, endAt, mode, status);
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
    before: [],
    duration: ["join"],
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
    before: [],
    duration: ["join"],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "none",
    before: ["apply"],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "invited",
    before: ["agree", "disagree"],
    duration: [],
    after: [],
  },
  {
    mode: ["manual", "invite"],
    status: "revoked",
    before: ["apply"],
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
    before: ["apply"],
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

const managerData: IAction[] = [
  {
    mode: ["manual", "invite"],
    status: "none",
    before: ["invite"],
    duration: ["invite"],
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
    before: ["invite"],
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
