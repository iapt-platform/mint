file(GLOB TEST_SOURCES ${CMAKE_CURRENT_SOURCE_DIR}/*.cpp)

foreach(sfn ${TEST_SOURCES})
    file(RELATIVE_PATH itn ${CMAKE_CURRENT_SOURCE_DIR} ${sfn})
    string(REPLACE ".cpp" "" it ${itn})
    add_executable(${it} ${itn})    
    target_link_libraries(${it} PRIVATE mint)
    set_target_properties(${it} PROPERTIES LINK_FLAGS_RELEASE -s)
endforeach(sfn ${TEST_SOURCES})

