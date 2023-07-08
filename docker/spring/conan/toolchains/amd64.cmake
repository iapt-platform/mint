set(CMAKE_SYSTEM_NAME Linux)
set(CMAKE_SYSTEM_PROCESSOR x86_64)

set(target x86_64-linux-gnu)
set(clang_version 15)

set(CMAKE_C_COMPILER clang-${clang_version})
set(CMAKE_C_COMPILER_TARGET ${target})
set(CMAKE_CXX_COMPILER clang++-${clang_version})
set(CMAKE_CXX_COMPILER_TARGET ${target})
set(CMAKE_CXX_FLAGS "-stdlib=libstdc++")
set(CMAKE_EXE_LINKER_FLAGS "--ld-path=mold -Wl,--build-id=sha1")
