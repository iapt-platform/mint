cmake_minimum_required(VERSION 3.21)
include(ExternalProject)
include(FetchContent)

project(
    mint
    VERSION 0.1.0
    DESCRIPTION "International Academy Of Pali Tipitaka"
    LANGUAGES C CXX)
# --------------------------------------------------------
# FIXME https://gcc.gnu.org/bugzilla/show_bug.cgi?id=100900
set(CMAKE_CXX_STANDARD 20)
set(CMAKE_CXX_STANDARD_REQUIRED ON)
set(CMAKE_CXX_EXTENSIONS OFF)

include(${CMAKE_BINARY_DIR}/conanbuildinfo.cmake)
conan_basic_setup()

# --------------------------------------------------------
execute_process(COMMAND git describe --tags --always --dirty
    OUTPUT_VARIABLE GIT_REV
    ERROR_QUIET
)
string(STRIP "${GIT_REV}" GIT_REV)
string(TIMESTAMP BUILD_TIME UTC)
configure_file(include/mint/version.h.in ${PROJECT_SOURCE_DIR}/include/mint/version.hpp)
# --------------------------------------------------------

add_subdirectory(loquat)
add_subdirectory(src)
add_subdirectory(apps)
add_subdirectory(tests)
